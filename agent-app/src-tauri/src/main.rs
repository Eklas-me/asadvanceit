#![cfg_attr(not(debug_assertions), windows_subsystem = "windows")]

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
use std::sync::atomic::{AtomicBool, AtomicUsize, Ordering};
static WEBRTC_GENERATION: AtomicUsize = AtomicUsize::new(0);
static WEBRTC_ACTIVE: AtomicBool = AtomicBool::new(false);
static WEBRTC_PAUSED: AtomicBool = AtomicBool::new(false);
static STREAMING_REQUESTED: AtomicBool = AtomicBool::new(false);

mod dxgi;
mod encoder;
mod webrtc_manager;

use webrtc_manager::WebRTCManager;
use dxgi::DXGICapture;
use encoder::MFEncoder;

use windows::Win32::UI::WindowsAndMessaging::{
    CreateWindowExW, DefWindowProcW, DispatchMessageW, GetMessageW, RegisterClassW, 
    CS_HREDRAW, CS_VREDRAW, CW_USEDEFAULT, WM_DEVICECHANGE, WNDCLASSW, WS_OVERLAPPEDWINDOW,
    MSG,
};
use windows::Win32::System::LibraryLoader::GetModuleHandleW;
use windows::core::{w};
use windows::Win32::Foundation::{HWND, LPARAM, LRESULT, WPARAM};

const DBT_DEVICEARRIVAL: usize = 0x8000;
static USB_APP_HANDLE: Mutex<Option<tauri::AppHandle>> = Mutex::const_new(None);
use std::path::PathBuf;
use std::fs;
use tauri::Manager;

const CONFIG_FILE: &str = "session.json";

#[derive(Serialize, Deserialize, Default)]
struct AppConfig {
    access_token: Option<String>,
    email: Option<String>,
    name: Option<String>,
    api_url: Option<String>,
}

fn get_config_path(handle: &tauri::AppHandle) -> PathBuf {
    let mut path = handle.path().app_config_dir().unwrap_or_else(|_| PathBuf::from("."));
    // Ensure the directory exists
    let _ = fs::create_dir_all(&path);
    path.push(CONFIG_FILE);
    path
}

fn save_session(handle: &tauri::AppHandle, token: String, email: String, name: String, api_url: String) {
    let config = AppConfig {
        access_token: Some(token),
        email: Some(email),
        name: Some(name),
        api_url: Some(api_url),
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
struct UserInfo {
    name: String,
    email: String,
}

#[derive(Serialize, Deserialize)]
struct LoginResponse {
    success: bool,
    message: String,
    magic_url: Option<String>,
    access_token: Option<String>,
    user: Option<UserInfo>,
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
    
    let hostname = hostname::get()
        .map(|h| h.to_string_lossy().into_owned())
        .unwrap_or_else(|_| "Unknown PC".to_string());
    
    let os_info = format!("{} {}", std::env::consts::OS, std::env::consts::ARCH);

    let response = client
        .post(&api_url)
        .json(&serde_json::json!({
            "email": email,
            "password": password,
            "device_name": hostname,
            "os_info": os_info,
            "browser": "Advance IT Client", // Hardcoded branding
            "platform": "Desktop App"
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
        let user_info: Option<UserInfo> = serde_json::from_value(body["user"].clone()).ok();
        
        // Start monitoring in background if we have a token
        if let Some(token) = access_token.clone() {
            // Save session for persistence
            let name = user_info.as_ref().map(|u| u.name.clone()).unwrap_or_default();
            save_session(&app_handle, token.clone(), email.clone(), name, api_url.clone());

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
            user: user_info,
        })
    } else {
        Ok(LoginResponse {
            success: false,
            message: body["message"].as_str().unwrap_or("Login failed").to_string(),
            magic_url: None,
            access_token: None,
            user: None,
        })
    }
}

#[tauri::command]
async fn logout(app_handle: tauri::AppHandle) -> Result<(), String> {
    clear_session(&app_handle);
    Ok(())
}

#[tauri::command]
async fn check_session(app_handle: tauri::AppHandle) -> Result<Option<UserInfo>, String> {
    if let Some(config) = load_session(&app_handle) {
        if let Some(token) = config.access_token {
            // If token exists, trigger monitoring and signaling if not already running
            let email = config.email.clone().unwrap_or_default();
            let name = config.name.clone().unwrap_or_default();
            println!(">>> Found existing session for {}", email);
            
            // Re-trigger monitoring (shared logic with login)
            let base_url = "https://asadvanceit.com"; // Default for now
            let stream_url = format!("{}/api/agent/stream", base_url);
            let hwid = machine_uid::get().unwrap_or_else(|_| "unknown".to_string());
            start_monitoring_background(stream_url, token.clone(), hwid);
            
            return Ok(Some(UserInfo { name, email }));
        }
    }
    Ok(None)
}

#[tauri::command]
async fn check_force_logout() -> Result<bool, String> {
    let flag_path = PathBuf::from("force_logout.flag");
    if flag_path.exists() {
        let _ = fs::remove_file(flag_path);
        Ok(true)
    } else {
        Ok(false)
    }
}

#[tauri::command]
async fn open_browser(url: String) -> Result<(), String> {
    open::that(&url).map_err(|e| format!("Failed to open browser: {}", e))
}

fn start_monitoring_background(stream_url: String, token: String, hardware_id: String) {
    tokio::spawn(async move {
        println!(">>> Starting monitoring loop to {}", stream_url);
        println!(">>> App Version: 2.2 (Robust Pause via HTTP)");
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

            // 2. Only capture image if admin requested streaming AND WebRTC is NOT active
            let is_webrtc_active = WEBRTC_ACTIVE.load(Ordering::Relaxed);
            let is_webrtc_paused = WEBRTC_PAUSED.load(Ordering::Relaxed);
            let is_streaming_requested = STREAMING_REQUESTED.load(Ordering::Relaxed);
            let mut image_b64 = String::new();

            // Only capture JPEGs if admin requested it AND no WebRTC stream is running
            if is_streaming_requested && !is_webrtc_active && !is_webrtc_paused {
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
                "hardware_id": hardware_id,
                "agent_version": "1.0.1"
            });

            if !image_b64.is_empty() {
                payload["image"] = json!(image_b64);
            }

            // 4. Send non-blocking and check response
            let res = client.post(&stream_url)
                .header("Authorization", format!("Bearer {}", token))
                .json(&payload)
                .send()
                .await;

            match res {
                Ok(response) => {
                    if let Ok(json) = response.json::<serde_json::Value>().await {
                        if let Some(requested) = json["stream_requested"].as_bool() {
                            let current = STREAMING_REQUESTED.load(Ordering::Relaxed);
                            if  requested != current {
                                println!(">>> Server requested stream state change: {} -> {}", current, requested);
                                STREAMING_REQUESTED.store(requested, Ordering::Relaxed);
                            }
                        }

                        if let Some(paused) = json["stream_paused"].as_bool() {
                            let current = WEBRTC_PAUSED.load(Ordering::Relaxed);
                            if  paused != current {
                                println!(">>> Server requested PAUSE state change: {} -> {}", current, paused);
                                WEBRTC_PAUSED.store(paused, Ordering::Relaxed);
                            }
                        }
                    }
                },
                Err(e) => println!(">>> Stream Upload Error: {}", e),
            }

            // Sleep intervals:
            // - 5s if NOT streaming (idle mode, just stats, saves bandwidth)
            // - 2s if WebRTC active or paused
            // - 1s if streaming via JPEG fallback
            let sleep_ms = if !is_streaming_requested {
                5000
            } else if is_webrtc_active || is_webrtc_paused {
                2000
            } else {
                1000
            };
            tokio::time::sleep(Duration::from_millis(sleep_ms)).await;
        }
    });
}

fn start_signaling_background(hwid: String, base_url: String) {
    thread::spawn(move || {
        let rt = tokio::runtime::Runtime::new().unwrap();
        rt.block_on(async {
            println!(">>> Starting Signaling WebSocket for HWID: {}", hwid);
            let ws_url = format!("wss://asadvanceit.com/app/reverb_app_key?protocol=7&client=js&version=8.4.0-rc2&flash=false");
            
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

                                        if event == "App\\Events\\ForceLogoutEvent" {
                                            println!(">>> Received Force Logout Event");
                                            let my_app_handle = webrtc_manager.lock().await; // hack to get an async context to wait briefly
                                            
                                            // Trigger Tauri frontend event (we will emit this from the app handle if we passed it down, 
                                            // but since we dont have the app handle in this thread directly easily, we can write a special file 
                                            // or we can use the existing control channel logic. Wait, let's just make it simpler: exit the process, 
                                            // or pass the handle. Since we don't have the handle, let's just use std::process::exit for a hard reset, 
                                            // or better yet, since we have check_session in JS, let's just clear the session file directly and exit).
                                            // Let's clear the session file and exit process. Tauri will restart or close. 
                                            // Wait, killing the process is harsh. Let's write to a "force_logout.flag" file.
                                            let _ = std::fs::write("force_logout.flag", "1");
                                            std::process::exit(0); // Exit the app. The user will have to open it again, or we restart it.
                                            
                                        } else if event == "webrtc.signal" {
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
                                                                
                                                                // Increment generation to kill old threads
                                                                let my_gen = WEBRTC_GENERATION.fetch_add(1, Ordering::Relaxed) + 1;

                                                                // Start Stream Loop
                                                                let mgr_clone = Arc::clone(&webrtc_manager);
                                                                let rt_handle = tokio::runtime::Handle::current();
                                                                
                                                                std::thread::spawn(move || {
                                                                    println!(">>> Starting WebRTC Video Stream Loop (Dedicated Thread)");
                                                                    let capture = DXGICapture::new().expect("DXGI Init Failed");
                                                                    let encoder = MFEncoder::new(1280, 720).expect("MF Encoder Init Failed");
                                                                    
                                                                    loop {
                                                                        // Check generation
                                                                        if WEBRTC_GENERATION.load(Ordering::Relaxed) != my_gen {
                                                                            println!(">>> Stopping Zombie WebRTC Thread (Gen: {})", my_gen);
                                                                            break;
                                                                        }

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
                                                    "start_stream" => {
                                                        println!(">>> ADMIN REQUESTED STREAM START");
                                                        STREAMING_REQUESTED.store(true, Ordering::Relaxed);
                                                        WEBRTC_PAUSED.store(false, Ordering::Relaxed);
                                                    },
                                                    "stop_stream" => {
                                                        println!(">>> ADMIN REQUESTED STREAM STOP");
                                                        STREAMING_REQUESTED.store(false, Ordering::Relaxed);
                                                        WEBRTC_PAUSED.store(true, Ordering::Relaxed);
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

fn start_usb_monitor(app_handle: tauri::AppHandle) {
    thread::spawn(move || {
        unsafe {
            let instance = GetModuleHandleW(None).unwrap();
            let window_class = w!("USB_MONITOR_CLASS");

            let wc = WNDCLASSW {
                hCursor: windows::Win32::UI::WindowsAndMessaging::HCURSOR::default(),
                hInstance: instance.into(),
                lpszClassName: window_class,
                style: CS_HREDRAW | CS_VREDRAW,
                lpfnWndProc: Some(wnd_proc),
                ..Default::default()
            };

            RegisterClassW(&wc);

            let hwnd = CreateWindowExW(
                windows::Win32::UI::WindowsAndMessaging::WINDOW_EX_STYLE::default(),
                window_class,
                w!("USB Monitor"),
                WS_OVERLAPPEDWINDOW,
                CW_USEDEFAULT,
                CW_USEDEFAULT,
                CW_USEDEFAULT,
                CW_USEDEFAULT,
                None,
                None,
                instance,
                None,
            );

            let hwnd = hwnd.unwrap();
            if hwnd.is_invalid() {
                return;
            }

            // Store app_handle for later use in wnd_proc
            tokio::runtime::Runtime::new().unwrap().block_on(async {
                USB_APP_HANDLE.lock().await.replace(app_handle);
            });

            let mut msg = MSG::default();
            while GetMessageW(&mut msg, None, 0, 0).as_bool() {
                DispatchMessageW(&msg);
            }
        }
    });
}

extern "system" fn wnd_proc(hwnd: HWND, msg: u32, wparam: WPARAM, lparam: LPARAM) -> LRESULT {
    // Log every WM_DEVICECHANGE to see if we get anything
    if msg == WM_DEVICECHANGE {
        println!(">>> WM_DEVICECHANGE received: wparam={:X}", wparam.0);
        // 0x07 = DBT_DEVNODES_CHANGED
        // 0x8004 = DBT_DEVICEREMOVECOMPLETE
        if wparam.0 == DBT_DEVICEARRIVAL || wparam.0 == 0x07 || wparam.0 == 0x8004 {
            println!(">>> USB/Device ARRIVAL, REMOVAL or CHANGE DETECTED!");
            
            // Trigger the notification
            thread::spawn(|| {
                let rt = tokio::runtime::Runtime::new().unwrap();
                rt.block_on(async {
                    if let Some(handle) = USB_APP_HANDLE.lock().await.as_ref() {
                        if let Err(e) = notify_usb_event(handle.clone()).await {
                            println!(">>> USB Notification Error: {}", e);
                        }
                    } else {
                        println!(">>> USB Notification Failed: AppHandle not found in Mutex");
                    }
                });
            });
        }
    }
    unsafe { DefWindowProcW(hwnd, msg, wparam, lparam) }
}

use std::collections::HashSet;

struct UsbState {
    known_devices: std::sync::Mutex<HashSet<String>>,
}

// ... (Other structs and imports remain, make sure not to delete them if outside range)

fn get_wpd_devices() -> Vec<String> {
    let output = std::process::Command::new("powershell")
        .args(&[
            "-NoProfile",
            "-Command",
            "Get-PnpDevice -Class WPD -PresentOnly | Select-Object -ExpandProperty FriendlyName"
        ])
        .output();

    if let Ok(o) = output {
        let stdout = String::from_utf8_lossy(&o.stdout);
        stdout.lines()
            .map(|s| s.trim().to_string())
            .filter(|s| !s.is_empty())
            .collect()
    } else {
        Vec::new()
    }
}

async fn notify_usb_event(handle: tauri::AppHandle) -> Result<(), String> {
    println!(">>> Preparing USB notification...");
    let session = load_session(&handle);
    let (token, api_url) = match session {
        Some(s) => (
            s.access_token.unwrap_or_default(),
            s.api_url.unwrap_or_else(|| "https://asadvanceit.com".to_string())
        ),
        None => {
            println!(">>> USB Notification Failed: No active session found.");
            return Err("No session".to_string());
        }
    };

    if token.is_empty() {
        println!(">>> USB Notification Failed: Access token is empty.");
        return Err("Not logged in".to_string());
    }

    println!(">>> Waiting 3 seconds for device stabilization...");
    tokio::time::sleep(tokio::time::Duration::from_secs(3)).await;

    // Capture current state of all devices
    let mut current_device_map: std::collections::HashMap<String, (String, String, u64)> = std::collections::HashMap::new();

    // 1. Scan Disks
    let mut disks = sysinfo::Disks::new();
    disks.refresh_list();
    for disk in &disks {
        if !matches!(disk.kind(), sysinfo::DiskKind::HDD | sysinfo::DiskKind::SSD) {
            let name = disk.name().to_string_lossy().to_string();
            let mount = disk.mount_point().to_string_lossy().to_string();
            let size = disk.total_space();
            // Key: Mount point is usually unique for disks
            let key = format!("DISK:{}", mount);
            current_device_map.insert(key, (name, mount, size));
        }
    }

    // 2. Scan WPD (Phones)
    let wpd_devices = get_wpd_devices();
    for name in wpd_devices {
        let key = format!("WPD:{}", name);
        current_device_map.insert(key, (name, "WPD/MTP".to_string(), 0));
    }

    // 3. Diff against known state
    let state = handle.state::<UsbState>();
    let mut new_device = None;
    
    {
        let mut known = state.known_devices.lock().unwrap();
        
        // Find devices that were in known but NOT in current (REMOVED)
        for key in known.iter() {
            if !current_device_map.contains_key(key) {
                println!(">>> DEVICE REMOVED: {}", key);
            }
        }

        // Find a device that is in current but NOT in known (NEW)
        for (key, data) in &current_device_map {
            if !known.contains(key) {
                // Found a new one!
                println!(">>> NEW DEVICE DETECTED: {}", key);
                // Prefer selecting this one. If multiple, we create a strategy (largest, or just first)
                if new_device.is_none() {
                    new_device = Some(data.clone());
                }
            }
        }

        // Update known state to match current (handle removals too)
        *known = current_device_map.keys().cloned().collect();
        println!(">>> Updated Known Devices: {:?}", known);
    }

    if let Some((usb_name, mount_point, total_space)) = new_device {
        println!(">>> Reporting New Device: {} ({})", usb_name, mount_point);
        
        let client = reqwest::Client::new();
        let usb_event_url = if api_url.contains("/api/agent/login") {
            api_url.replace("/api/agent/login", "/api/agent/usb-event")
        } else {
            format!("{}/api/agent/usb-event", api_url.trim_end_matches('/').trim_end_matches("/api/agent/login"))
        };

        let payload = json!({
            "name": usb_name,
            "mount": mount_point,
            "total_space": total_space,
        });

        println!(">>> Sending USB notification to: {}", usb_event_url);

        let response = client.post(&usb_event_url)
            .header("Authorization", format!("Bearer {}", token))
            .json(&payload)
            .send()
            .await
            .map_err(|e| format!("Network error: {}", e))?;

        println!(">>> Server responded with status: {}", response.status());
    } else {
        println!(">>> Scan Complete. No NEW devices detected vs Known State.");
    }

    Ok(())
}

fn main() {
    tauri::Builder::default()
        .plugin(tauri_plugin_updater::Builder::new().build())
        .manage(UsbState {
            known_devices: std::sync::Mutex::new(HashSet::new()),
        })
        .setup(|app| {
            // Initialize Known Devices State
            let state = app.state::<UsbState>();
            let mut known = state.known_devices.lock().unwrap();
            
            println!(">>> Initial Device Scan...");
            // Scan Disks
            let mut disks = sysinfo::Disks::new();
            disks.refresh_list();
            for disk in &disks {
                if !matches!(disk.kind(), sysinfo::DiskKind::HDD | sysinfo::DiskKind::SSD) {
                    let mount = disk.mount_point().to_string_lossy().to_string();
                    let key = format!("DISK:{}", mount);
                    known.insert(key);
                }
            }
            // Scan WPD
            for name in get_wpd_devices() {
                let key = format!("WPD:{}", name);
                known.insert(key);
            }
            println!(">>> Initial Known Devices: {:?}", known);

            // Start Signaling in Background
            let hwid = machine_uid::get().unwrap_or_else(|_| "unknown".to_string());
            start_signaling_background(hwid, "https://asadvanceit.com".to_string());

            // Start USB Monitoring
            start_usb_monitor(app.handle().clone());

            Ok(())
        })
        .invoke_handler(tauri::generate_handler![
            login,
            logout,
            check_session,
            check_force_logout,
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
