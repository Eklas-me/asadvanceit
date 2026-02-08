// use image::codecs::jpeg::JpegEncoder;
use image::ImageOutputFormat;
use serde::{Deserialize, Serialize};
use serde_json::json;
use std::io::Cursor;
use std::thread;
use std::time::Duration;
use sysinfo::System;
use base64::{Engine as _, engine::general_purpose};
// use windows::Win32::Graphics::Direct3D11::{ID3D11Texture2D, D3D11_TEXTURE2D_DESC};
use std::sync::Arc;
use tokio::sync::Mutex;
use std::sync::atomic::{AtomicBool, Ordering};
static WEBRTC_ACTIVE: AtomicBool = AtomicBool::new(false);
static WEBRTC_PAUSED: AtomicBool = AtomicBool::new(false);

mod dxgi;
mod encoder;
mod webrtc_manager;

use webrtc_manager::WebRTCManager;
use dxgi::DXGICapture;
use encoder::MFEncoder;
use std::path::PathBuf;
use std::fs;
use tauri::Manager;

const CONFIG_FILE: &str = "session.json";

#[derive(Serialize, Deserialize, Default)]
struct AppConfig {
    access_token: Option<String>,
    email: Option<String>,
}

fn get_config_path(handle: &tauri::AppHandle) -> PathBuf {
    let mut path = handle.path().app_config_dir().unwrap_or_else(|_| PathBuf::from("."));
    // Ensure the directory exists
    let _ = fs::create_dir_all(&path);
    path.push(CONFIG_FILE);
    path
}

fn save_session(handle: &tauri::AppHandle, token: String, email: String) {
    let config = AppConfig {
        access_token: Some(token),
        email: Some(email),
    };
    if let Ok(content) = serde_json::to_string(&config) {
        let _ = fs::write(get_config_path(handle), content);
    }
}

fn load_session(handle: &tauri::AppHandle) -> Option<AppConfig> {
    if let Ok(content) = fs::read_to_string(get_config_path(handle)) {
        return serde_json::from_str(&content).ok();
    }
    None
}

fn clear_session(handle: &tauri::AppHandle) {
    let _ = fs::remove_file(get_config_path(handle));
}

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
async fn login(app_handle: tauri::AppHandle, email: String, password: String, api_url: String) -> Result<LoginResponse, String> {
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
            // Save session for persistence
            save_session(&app_handle, token.clone(), email.clone());

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
async fn logout(app_handle: tauri::AppHandle) -> Result<(), String> {
    clear_session(&app_handle);
    Ok(())
}

#[tauri::command]
async fn check_session(app_handle: tauri::AppHandle) -> Result<Option<String>, String> {
    if let Some(config) = load_session(&app_handle) {
        if let Some(token) = config.access_token {
            // If token exists, trigger monitoring and signaling if not already running
            // Note: In a real app, you might want to validate the token with the server here
            let email = config.email.unwrap_or_default();
            println!(">>> Found existing session for {}", email);
            
            // Re-trigger monitoring (shared logic with login)
            let base_url = "https://test.asadvanceit.com"; // Default for now
            let stream_url = format!("{}/api/agent/stream", base_url);
            let hwid = machine_uid::get().unwrap_or_else(|_| "unknown".to_string());
            start_monitoring_background(stream_url, token.clone(), hwid);
            
            return Ok(Some(email));
        }
    }
    Ok(None)
}

#[tauri::command]
async fn open_browser(url: String) -> Result<(), String> {
    open::that(&url).map_err(|e| format!("Failed to open browser: {}", e))
}

fn start_monitoring_background(stream_url: String, token: String, hardware_id: String) {
    tokio::spawn(async move {
        println!(">>> Starting monitoring loop to {}", stream_url);
        let client = reqwest::Client::new();
        let mut sys = System::new_all();
        
        loop {
            // 1. Collect Stats
            sys.refresh_cpu();
            sys.refresh_memory();
            
            let stats = json!({
                "cpu": sys.global_cpu_info().cpu_usage(),
                "ram_used": sys.used_memory(),
                "ram_total": sys.total_memory()
            });

            // 2. Logic: Only capture image if WebRTC is NOT active AND NOT paused
            let is_webrtc_active = WEBRTC_ACTIVE.load(Ordering::Relaxed);
            let is_webrtc_paused = WEBRTC_PAUSED.load(Ordering::Relaxed);
            let mut image_b64 = String::new();

            // Only capture JPEGs if no high-speed stream is running or if the user is just monitoring in background
            if !is_webrtc_active && !is_webrtc_paused {
                let screens = screenshots::Screen::all().unwrap_or_default();
                if let Some(screen) = screens.first() {
                    if let Ok(image) = screen.capture() {
                        let dynamic_image = image::DynamicImage::ImageRgba8(image);
                        let mut buffer = Vec::new();
                        let mut cursor = Cursor::new(&mut buffer);
                        let resized = dynamic_image.thumbnail(1280, 720);
                        if resized.write_to(&mut cursor, ImageOutputFormat::Jpeg(75)).is_ok() {
                            image_b64 = general_purpose::STANDARD.encode(&buffer);
                        }
                    }
                }
            }

            // 3. Prepare Payload
            let mut payload = json!({
                "stats": stats,
                "hardware_id": hardware_id
            });

            if !image_b64.is_empty() {
                payload["image"] = json!(image_b64);
            }

            // 4. Send non-blocking
            let res = client.post(&stream_url)
                .header("Authorization", format!("Bearer {}", token))
                .json(&payload)
                .send()
                .await;

            if let Err(e) = res {
                println!(">>> Stream Upload Error: {}", e);
            }

            // Sleep: 
            // - 2s if WebRTC active or paused (saving network)
            // - 1s (1000ms) if in pure background monitoring mode (prevent hammering at 33ms)
            let sleep_ms = if is_webrtc_active || is_webrtc_paused { 2000 } else { 1000 };
            tokio::time::sleep(Duration::from_millis(sleep_ms)).await;
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
                                        // println!(">>> Received Event: {} on channel {}", event, data["channel"].as_str().unwrap_or(""));

                                        if event == "webrtc.signal" {
                                            let raw_data = &data["data"];
                                            let signal_data = if raw_data.is_string() {
                                                serde_json::from_str::<serde_json::Value>(raw_data.as_str().unwrap()).unwrap_or_default()
                                            } else {
                                                raw_data.clone()
                                            };
                                            
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
                                                                WEBRTC_ACTIVE.store(true, Ordering::Relaxed);
                                                                
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
                                                                            // Only process frames if the DataChannel is ready
                                                                            if let Some(manager) = mg.as_ref() {
                                                                                if !manager.is_data_channel_ready().await {
                                                                                    return None;
                                                                                }
                                                                            }
                                                                            Some(())
                                                                        });

                                                                        if result.is_none() || WEBRTC_PAUSED.load(Ordering::Relaxed) { 
                                                                            std::thread::sleep(std::time::Duration::from_millis(100)); // Sleep more when paused
                                                                            continue; 
                                                                        }

                                                                        let _frame_start = std::time::Instant::now();
                                                                        
                                                                        if let Ok(captured) = capture.capture_frame() {
                                                                            // Hardware H.264 Encoding Path (The "AnyDesk" way)
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
                                                                        
                                                                        // Target ~30 FPS (33ms)
                                                                        let elapsed = _frame_start.elapsed().as_millis() as u64;
                                                                        let sleep_dur = if elapsed < 33 { 33 - elapsed } else { 1 };
                                                                        std::thread::sleep(std::time::Duration::from_millis(sleep_dur));
                                                                    }
                                                                });
                                                            },
                                                            Err(e) => println!(">>> Offer Handle Error: {}", e),
                                                        }
                                                    },
                                                    Err(e) => println!(">>> WebRTC Manager Init Error: {}", e),
                                                }
                                            } else if sig_type == "candidate" {
                                                let mg = webrtc_manager.lock().await;
                                                if let Some(manager) = mg.as_ref() {
                                                    let cand = serde_json::from_value(signal_data["candidate"].clone()).unwrap_or_default();
                                                    let _ = manager.add_ice_candidate(cand).await;
                                                }
                                            }
                                        } else if event == "control" {
                                            let raw_data = &data["data"];
                                            let control_data = if raw_data.is_string() {
                                                serde_json::from_str::<serde_json::Value>(raw_data.as_str().unwrap()).unwrap_or_default()
                                            } else {
                                                raw_data.clone()
                                            };

                                            if let Some(action) = control_data["action"].as_str() {
                                                println!(">>> Received Control Action: {}", action);
                                                match action {
                                                    "stop_capture" => {
                                                        println!(">>> PAUSING STREAM");
                                                        WEBRTC_PAUSED.store(true, Ordering::Relaxed);
                                                    },
                                                    "start_capture" => {
                                                        println!(">>> RESUMING STREAM");
                                                        WEBRTC_PAUSED.store(false, Ordering::Relaxed);
                                                    },
                                                    _ => {}
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
            logout,
            check_session,
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
