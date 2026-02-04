#![cfg_attr(not(debug_assertions), windows_subsystem = "windows")]

use serde::{Deserialize, Serialize};
use serde_json::json;
use std::sync::{Arc, Mutex};
use std::thread;
use std::time::Duration;
use sysinfo::{System, Networks, Components, Disks}; // Updated for sysinfo 0.30
use tokio_tungstenite::{connect_async, tungstenite::protocol::Message};
use futures_util::{SinkExt, StreamExt};
use url::Url;
use std::io::Cursor;
use image::ImageOutputFormat;
use base64::{Engine as _, engine::general_purpose};

#[derive(Serialize, Deserialize)]
struct LoginResponse {
    success: bool,
    message: String,
    magic_url: Option<String>,
}

// Global state to control monitoring
struct AgentState {
    monitoring_active: bool,
}

#[tauri::command]
async fn login(email: String, password: String, api_url: String) -> Result<LoginResponse, String> {
    let client = reqwest::Client::new();
    
    let response = client
        .post(&api_url)
        .json(&serde_json::json!({
            "email": email,
            "password": password
        }))
        .send()
        .await
        .map_err(|e| format!("Connection failed: {}", e))?;

    let status = response.status();
    let body: serde_json::Value = response
        .json()
        .await
        .map_err(|e| format!("Invalid response: {}", e))?;

    if status.is_success() && body["success"].as_bool().unwrap_or(false) {
        // Start monitoring in background upon successful login
        start_monitoring_background();

        Ok(LoginResponse {
            success: true,
            message: body["message"].as_str().unwrap_or("Success").to_string(),
            magic_url: body["magic_url"].as_str().map(|s| s.to_string()),
        })
    } else {
        Ok(LoginResponse {
            success: false,
            message: body["message"].as_str().unwrap_or("Login failed").to_string(),
            magic_url: None,
        })
    }
}

#[tauri::command]
async fn open_browser(url: String) -> Result<(), String> {
    open::that(&url).map_err(|e| format!("Failed to open browser: {}", e))
}

fn start_monitoring_background() {
    thread::spawn(|| {
        let rt = tokio::runtime::Runtime::new().unwrap();
        rt.block_on(async {
            connect_and_monitor().await;
        });
    });
}

async fn connect_and_monitor() {
    let server_url = "ws://localhost:3001/ws"; // Configurable in future
    let url = Url::parse(server_url).expect("Invalid WebSocket URL");

    println!("Attempting to connect to {}", server_url);

    match connect_async(url).await {
        Ok((ws_stream, _)) => {
            println!("Connected to Admin Server!");
            let (mut write, mut _read) = ws_stream.split();

            // Stats collecting
            let mut sys = System::new_all();
            
            // Loop for sending data
            loop {
                // 1. Collect Stats
                sys.refresh_cpu();
                sys.refresh_memory();
                
                let cpu_usage = sys.global_cpu_info().cpu_usage();
                let ram_used = sys.used_memory();
                let ram_total = sys.total_memory();
                
                let stats_json = json!({
                    "type": "stats",
                    "cpu": cpu_usage,
                    "ram_used": ram_used,
                    "ram_total": ram_total
                });

                if let Err(e) = write.send(Message::Text(stats_json.to_string())).await {
                    println!("Failed to send stats: {}", e);
                    break;
                }

                // 2. Capture Screen
                let screens = screenshots::Screen::all().unwrap_or_default();
                if let Some(screen) = screens.first() {
                     if let Ok(image) = screen.capture() {
                         // Fix for screenshots 0.8 to image 0.24 conversion
                         // image.rgba() returns Vec<u8> which we can use directly
                         if let Some(img) = image::RgbaImage::from_raw(image.width(), image.height(), image.rgba().clone()) {
                             let dynamic_image = image::DynamicImage::ImageRgba8(img);
                             let mut buffer = Vec::new();
                             let mut cursor = Cursor::new(&mut buffer);
                             // Resize to reduce payload size (optional but good for performance)
                             let resized = dynamic_image.thumbnail(800, 600);
                             
                             if resized.write_to(&mut cursor, ImageOutputFormat::Jpeg(60)).is_ok() {
                                 let b64 = general_purpose::STANDARD.encode(&buffer);
                                 let screen_json = json!({
                                     "type": "screen",
                                     "image": b64
                                 });
                                 if let Err(e) = write.send(Message::Text(screen_json.to_string())).await {
                                     println!("Failed to send screen: {}", e);
                                     break;
                                 }
                             }
                         }
                     }
                }

                tokio::time::sleep(Duration::from_secs(2)).await;
            }
        }
        Err(e) => {
            println!("WebSocket connection failed: {}. Retrying in 5s...", e);
            tokio::time::sleep(Duration::from_secs(5)).await;
        }
    }
}

fn main() {
    tauri::Builder::default()
        .invoke_handler(tauri::generate_handler![login, open_browser])
        .run(tauri::generate_context!())
        .expect("error while running tauri application");
}
