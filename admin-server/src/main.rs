use axum::{
    extract::{ws::{Message, WebSocket, WebSocketUpgrade}, State},
    response::{Html, IntoResponse},
    routing::get,
    Router,
};
use dashmap::DashMap;
use futures::{sink::SinkExt, stream::StreamExt};
use std::{net::SocketAddr, sync::Arc};
use tower_http::{
    services::ServeDir,
    cors::CorsLayer,
};

// Application State
struct AppState {
    // Shared broadcast channel for all connected clients
    tx: tokio::sync::broadcast::Sender<String>,
}

#[tokio::main]
async fn main() {
    tracing_subscriber::fmt::init();

    // Create a broadcast channel with capacity 100 messages
    let (tx, _rx) = tokio::sync::broadcast::channel(100);

    let state = Arc::new(AppState { tx });

    // CORS config (allow all to avoid mixed content issues with localhost)
    let cors = CorsLayer::permissive();

    let app = Router::new()
        .route("/", get(index_handler)) // Dashboard UI
        .route("/ws", get(ws_handler))  // WebSocket endpoint
        .nest_service("/assets", ServeDir::new("assets"))
        .layer(cors)
        .with_state(state);

    let port = 3001;
    let addr = SocketAddr::from(([0, 0, 0, 0], port));
    println!(">>> Admin Server running on http://{}", addr);

    let listener = tokio::net::TcpListener::bind(addr).await.unwrap();
    axum::serve(listener, app).await.unwrap();
}

async fn index_handler() -> Html<&'static str> {
    Html(include_str!("../index.html"))
}

async fn ws_handler(
    ws: WebSocketUpgrade,
    State(state): State<Arc<AppState>>,
) -> impl IntoResponse {
    ws.on_upgrade(|socket| handle_socket(socket, state))
}

async fn handle_socket(socket: WebSocket, state: Arc<AppState>) {
    let (mut sender, mut receiver) = socket.split();
    
    // Subscribe to broadcast channel
    let mut rx = state.tx.subscribe();

    // Spawn a task to forward broadcast messages to this client
    let mut send_task = tokio::spawn(async move {
        while let Ok(msg) = rx.recv().await {
            // In a real app, we wouldn't send back to the same client if we tracked IDs
            // But for simple broadcast, echo to all is fine (client filters)
            if sender.send(Message::Text(msg)).await.is_err() {
                break;
            }
        }
    });

    // Handle incoming messages from this client
    let tx = state.tx.clone();
    let mut recv_task = tokio::spawn(async move {
        while let Some(Ok(msg)) = receiver.next().await {
            if let Message::Text(text) = msg {
                // Broadcast received message to all other subscribers
                let _ = tx.send(text);
            }
        }
    });

    // Wait for either task to finish (disconnection)
    tokio::select! {
        _ = (&mut send_task) => recv_task.abort(),
        _ = (&mut recv_task) => send_task.abort(),
    };
}
