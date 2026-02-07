use image::ImageOutputFormat;
use serde::{Deserialize, Serialize};
use serde_json::json;
use std::io::Cursor;
use std::thread;
use std::time::Duration;
use sysinfo::System;
use base64::{Engine as _, engine::general_purpose};
use std::sync::Arc;
use tokio::sync::Mutex;

mod dxgi;
mod encoder;
mod webrtc_manager;

use webrtc_manager::WebRTCManager;
use dxgi::DXGICapture;
use encoder::MFEncoder;

#[derive(Serialize, Deserialize)]
struct LoginResponse {
    success: bool,
    message: String,
    magic_url: Option<String>,
    access_token: Option<String>,
}

#[tauri::command]
fn get_hwid() -> String {
    machine_uid::get().unwrap_or_else(|_| "unknown_hwid".to_string())
}

#[tauri::command]
fn get_computer_name() -> String {
    hostname::get()
        .map(|h| h.to_string_lossy().into_owned())
        .unwrap_or_else(|_| "Unknown PC".to_string())
}

#[tauri::command]
async fn login(email: String, password: String, api_url: String) -> Result<LoginResponse, String> {
    println!(">>> Rust: Received login request for {} to URL {}", email, api_url);
    let client = reqwest::Client::new();
    
    let response = client
        .post(&api_url)
        .json(&serde_json::json!({
            "email": email,
            "password": password
        }))
        .send()
        .await
        .map_err(|e| {
            let err_msg = format!("Rust Connection Error: {} ({:?})", e, e);
            println!(">>> {}", err_msg);
            err_msg
        })?;

    println!(">>> Rust: Received response status: {}", response.status());

    let status = response.status();
    let body: serde_json::Value = response
        .json()
        .await
        .map_err(|e| format!("Invalid response: {}", e))?;

    if status.is_success() && body["success"].as_bool().unwrap_or(false) {
        let magic_url = body["magic_url"].as_str().map(|s| s.to_string());
        let access_token = body["access_token"].as_str().map(|s| s.to_string());
        
        // Start monitoring in background if we have a token
        if let Some(token) = access_token.clone() {
            // Determine stream URL from login URL (replace /login with /stream)
            // Assuming api_url ends with /api/agent/login
            let stream_url = api_url.replace("/login", "/stream");
            let hwid = machine_uid::get().unwrap_or_else(|_| "unknown".to_string());
            start_monitoring_background(stream_url, token, hwid);
        }

        Ok(LoginResponse {
            success: true,
            message: body["message"].as_str().unwrap_or("Success").to_string(),
            magic_url,
            access_token,
        })
    } else {
        Ok(LoginResponse {
            success: false,
            message: body["message"].as_str().unwrap_or("Login failed").to_string(),
            magic_url: None,
            access_token: None,
        })
    }
}

#[tauri::command]
async fn open_browser(url: String) -> Result<(), String> {
    open::that(&url).map_err(|e| format!("Failed to open browser: {}", e))
}

fn start_monitoring_background(stream_url: String, token: String, hardware_id: String) {
    thread::spawn(move || {
        println!(">>> Starting monitoring loop to {}", stream_url);
        let client = reqwest::blocking::Client::new();
        let mut sys = System::new_all();
        
        loop {
            // 1. Collect Stats
            sys.refresh_cpu();
            sys.refresh_memory();
            
            let cpu_usage = sys.global_cpu_info().cpu_usage();
            let ram_used = sys.used_memory();
            let ram_total = sys.total_memory();
            
            let stats = json!({
                "cpu": cpu_usage,
                "ram_used": ram_used,
                "ram_total": ram_total
            });

            // 2. Capture Screen
            let mut image_b64 = String::new();
            let screens = screenshots::Screen::all().unwrap_or_default();
            if let Some(screen) = screens.first() {
                if let Ok(image) = screen.capture() {
                    let dynamic_image = image::DynamicImage::ImageRgba8(image);
                    let mut buffer = Vec::new();
                    let mut cursor = Cursor::new(&mut buffer);
                    // Resize to 720p for HD monitoring since user has good internet
                    let resized = dynamic_image.thumbnail(1280, 720);
                    
                    if resized.write_to(&mut cursor, ImageOutputFormat::Jpeg(75)).is_ok() {
                        image_b64 = general_purpose::STANDARD.encode(&buffer);
                    }
                }
            }

            if !image_b64.is_empty() {
                let payload = json!({
                    "image": image_b64,
                    "stats": stats,
                    "hardware_id": hardware_id
                });

                let res = client.post(&stream_url)
                    .header("Authorization", format!("Bearer {}", token))
                    .json(&payload)
                    .send();

                if let Err(e) = res {
                    println!(">>> Stream Upload Error: {}", e);
                }
            }

            // Adjust FPS: 200ms = 5 FPS for very smooth monitoring
            thread::sleep(Duration::from_millis(200));
        }
    });
}

fn start_signaling_background(hwid: String, base_url: String) {
    thread::spawn(move || {
        let rt = tokio::runtime::Runtime::new().unwrap();
        rt.block_on(async {
            println!(">>> Starting Signaling WebSocket for HWID: {}", hwid);
            let ws_url = format!("wss://test.asadvanceit.com/app/reverb_app_key?protocol=7&client=js&version=8.4.0-rc2&flash=false");
            
            let webrtc_manager: Arc<Mutex<Option<WebRTCManager>>> = Arc::new(Mutex::new(None));
            let hwid_clone = hwid.clone();
            let base_url_clone = base_url.clone();

            loop {
                match tokio_tungstenite::connect_async(&ws_url).await {
                    Ok((mut ws_stream, _)) => {
                        println!(">>> Connected to Signaling Server");
                        
                        let subscribe_msg = json!({
                            "event": "pusher:subscribe",
                            "data": { "channel": format!("device-control.{}", hwid) }
                        });
                        
                        use futures_util::SinkExt;
                        use futures_util::StreamExt;
                        
                        if let Err(e) = ws_stream.send(tokio_tungstenite::tungstenite::Message::Text(subscribe_msg.to_string().into())).await {
                            println!(">>> Subscription Error: {}", e);
                            continue;
                        }
                        
                        while let Some(msg) = ws_stream.next().await {
                            match msg {
                                Ok(tokio_tungstenite::tungstenite::Message::Text(text)) => {
                                    if let Ok(data) = serde_json::from_str::<serde_json::Value>(&text) {
                                        let event = data["event"].as_str().unwrap_or("");
                                        if event == "webrtc.signal" {
                                            let signal_data = data["data"].as_str().map(|s| serde_json::from_str::<serde_json::Value>(s).unwrap_or_default()).unwrap_or_default();
                                            let sig_type = signal_data["type"].as_str().unwrap_or("");
                                            
                                            if sig_type == "offer" {
                                                println!(">>> Received WebRTC Offer");
                                                match WebRTCManager::new().await {
                                                    Ok(manager) => {
                                                        let sdp = signal_data["sdp"].as_str().unwrap_or("");
                                                        match manager.handle_offer(sdp).await {
                                                            Ok(answer_sdp) => {
                                                                send_signal(&base_url_clone, &hwid_clone, json!({ "type": "answer", "sdp": answer_sdp })).await;
                                                                let mut mg = webrtc_manager.lock().await;
                                                                *mg = Some(manager);
                                                                
                                                                // Start Stream Loop
                                                                let mgr_clone = Arc::clone(&webrtc_manager);
                                                                let rt_handle = tokio::runtime::Handle::current();
                                                                
                                                                std::thread::spawn(move || {
                                                                    println!(">>> Starting WebRTC Video Stream Loop (Dedicated Thread)");
                                                                    let capture = DXGICapture::new().expect("DXGI Init Failed");
                                                                    let encoder = MFEncoder::new(1280, 720).expect("MF Encoder Init Failed");
                                                                    
                                                                    loop {
                                                                        let result = rt_handle.block_on(async {
                                                                            let mg = mgr_clone.lock().await;
                                                                            if mg.is_none() { return None; }
                                                                            
                                                                            // We still need to capture and encode OUTSIDE the lock for performance
                                                                            // but we can't easily do that if we need the manager.
                                                                            // Actually, send_video_packet is the only async part.
                                                                            Some(())
                                                                        });

                                                                        if result.is_none() { break; }

                                                                        if let Ok(captured) = capture.capture_frame() {
                                                                            if let Ok(raw_bytes) = capture.get_texture_bytes(&captured.texture) {
                                                                                // Compress to JPEG using image crate
                                                                                let img = image::ImageBuffer::<image::Rgba<u8>, _>::from_raw(1280, 720, raw_bytes);
                                                                                if let Some(img) = img {
                                                                                    let mut jpg_bytes = Vec::new();
                                                                                    let mut cursor = Cursor::new(&mut jpg_bytes);
                                                                                    let _ = img.write_to(&mut cursor, image::ImageFormat::Jpeg);

                                                                                    if !jpg_bytes.is_empty() {
                                                                                        let _ = rt_handle.block_on(async {
                                                                                            let mg = mgr_clone.lock().await;
                                                                                            if let Some(manager) = mg.as_ref() {
                                                                                                // Send as binary over DataChannel
                                                                                                let _ = manager.send_data(jpg_bytes).await;
                                                                                            }
                                                                                        });
                                                                                    }
                                                                                }
                                                                            }

                                                                            if let Ok(packet) = encoder.encode_frame(&captured.texture) {
                                                                                if !packet.is_empty() {
                                                                                    let _ = rt_handle.block_on(async {
                                                                                        let mg = mgr_clone.lock().await;
                                                                                        if let Some(manager) = mg.as_ref() {
                                                                                            let _ = manager.send_video_packet(packet).await;
                                                                                        }
                                                                                    });
                                                                                }
                                                                            }
                                                                        }
                                                                        
                                                                        std::thread::sleep(std::time::Duration::from_millis(30));
                                                                    }
                                                                    println!(">>> WebRTC Video Stream Loop Ended");
                                                                });
                                                            },
                                                            Err(e) => println!(">>> Offer Handle Error: {}", e),
                                                        }
                                                    },
                                                    Err(e) => println!(">>> WebRTC Manager Init Error: {}", e),
                                                }
                                            } else if sig_type == "candidate" {
                                                let mut mg = webrtc_manager.lock().await;
                                                if let Some(manager) = mg.as_ref() {
                                                    let cand = serde_json::from_value(signal_data["candidate"].clone()).unwrap_or_default();
                                                    let _ = manager.add_ice_candidate(cand).await;
                                                }
                                            }
                                        }
                                    }
                                },
                                Ok(_) => {},
                                Err(e) => { println!(">>> WS Read Error: {}", e); break; }
                            }
                        }
                    },
                    Err(e) => {
                        println!(">>> WS Connect Error: {}, retrying...", e);
                        tokio::time::sleep(tokio::time::Duration::from_secs(5)).await;
                    }
                }
            }
        });
    });
}

async fn send_signal(base_url: &str, hwid: &str, payload: serde_json::Value) {
    let client = reqwest::Client::new();
    let url = format!("{}/api/agent/signal", base_url);
    let target_channel = format!("agent-monitor.device.{}", hwid);
    
    let _ = client.post(&url)
        .json(&json!({
            "payload": payload,
            "target_channel": target_channel
        }))
        .send()
        .await;
}

fn main() {
    tauri::Builder::default()
        .setup(|_app| {
            // Start Signaling in Background
            let hwid = machine_uid::get().unwrap_or_else(|_| "unknown".to_string());
            start_signaling_background(hwid, "https://test.asadvanceit.com".to_string());
            Ok(())
        })
        .invoke_handler(tauri::generate_handler![
            login,
            open_browser,
            get_hwid,
            get_computer_name
        ])
        .on_window_event(|window, event| match event {
            tauri::WindowEvent::CloseRequested { api, .. } => {
                window.hide().unwrap();
                api.prevent_close();
            }
            _ => {}
        })
        .run(tauri::generate_context!())
        .expect("error while running tauri application");
}
