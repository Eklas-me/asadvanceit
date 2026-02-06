use image::ImageOutputFormat;
use serde::{Deserialize, Serialize};
use serde_json::json;
use std::io::Cursor;
use std::thread;
use std::time::Duration;
use sysinfo::System;
// use url::Url; // Removed unused
use base64::{Engine as _, engine::general_purpose};

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
                    // Resize to smaller size for much faster transmission and smoothness
                    let resized = dynamic_image.thumbnail(640, 480);
                    
                    if resized.write_to(&mut cursor, ImageOutputFormat::Jpeg(60)).is_ok() {
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

fn main() {
    tauri::Builder::default()
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
