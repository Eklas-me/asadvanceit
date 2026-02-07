use std::sync::Arc;
use tokio::sync::Mutex;
use webrtc::api::media_engine::MediaEngine;
use webrtc::api::APIBuilder;
use webrtc::peer_connection::configuration::RTCConfiguration;
use webrtc::peer_connection::RTCPeerConnection;
use webrtc::track::track_local::track_local_static_sample::TrackLocalStaticSample;
use webrtc::track::track_local::TrackLocal;
use webrtc::media::Sample;
use webrtc::data_channel::RTCDataChannel;
use webrtc::data_channel::data_channel_state::RTCDataChannelState;

pub struct WebRTCManager {
    peer_connection: Arc<RTCPeerConnection>,
    video_track: Arc<TrackLocalStaticSample>,
    data_channel: Arc<Mutex<Option<Arc<RTCDataChannel>>>>,
}

impl WebRTCManager {
    pub async fn new() -> webrtc::error::Result<Self> {
        let mut m = MediaEngine::default();
        m.register_default_codecs()?;
        
        let api = APIBuilder::new().with_media_engine(m).build();
        
        let config = RTCConfiguration {
            ice_servers: vec![webrtc::ice_transport::ice_server::RTCIceServer {
                urls: vec!["stun:stun.l.google.com:19302".to_owned()],
                ..Default::default()
            }],
            ..Default::default()
        };
        
        let peer_connection = Arc::new(api.new_peer_connection(config).await?);
        
        let data_channel = Arc::new(Mutex::new(None));
        let dc_clone = Arc::clone(&data_channel);
        
        // Let's create the DataChannel proactively from the Agent side as well
        let dc_init = webrtc::data_channel::data_channel_init::RTCDataChannelInit {
            ordered: Some(false), // Better for video frames
            max_retransmits: Some(0), // No retransmits = lower latency
            ..Default::default()
        };
        
        let local_dc = peer_connection.create_data_channel("video", Some(dc_init)).await?;
        println!(">>> Created local DataChannel: video");
        
        {
            let mut d = data_channel.lock().await;
            *d = Some(local_dc);
        }

        peer_connection.on_data_channel(Box::new(move |dc: Arc<RTCDataChannel>| {
            let dc_clone = Arc::clone(&dc_clone);
            let label = dc.label().to_string();
            Box::pin(async move {
                println!(">>> Remote peer created DataChannel: {}", label);
                if label == "video" {
                    let mut d = dc_clone.lock().await;
                    *d = Some(dc);
                }
            })
        }));

        let video_track = Arc::new(TrackLocalStaticSample::new(
            webrtc::rtp_transceiver::rtp_codec::RTCRtpCodecCapability {
                mime_type: "video/h264".to_owned(),
                ..Default::default()
            },
            "video".to_owned(),
            "webrtc-rs".to_owned(),
        ));
        
        peer_connection.add_track(Arc::clone(&video_track) as Arc<dyn TrackLocal + Send + Sync>).await?;
        
        Ok(Self {
            peer_connection,
            video_track,
            data_channel,
        })
    }

    pub async fn handle_offer(&self, sdp: &str) -> webrtc::error::Result<String> {
        let mut desc = webrtc::peer_connection::sdp::session_description::RTCSessionDescription::default();
        desc.sdp = sdp.to_owned();
        desc.sdp_type = webrtc::peer_connection::sdp::sdp_type::RTCSdpType::Offer;

        self.peer_connection.set_remote_description(desc).await?;
        
        let answer = self.peer_connection.create_answer(None).await?;
        
        // Create a channel to wait for ICE gathering to complete
        let mut gather_complete = self.peer_connection.gathering_complete_promise().await;
        
        self.peer_connection.set_local_description(answer.clone()).await?;
        
        // Wait for ICE gathering to complete
        let _ = gather_complete.recv().await;
        
        // Return the updated SDP with candidates
        if let Some(local_desc) = self.peer_connection.local_description().await {
            Ok(local_desc.sdp)
        } else {
            Ok(answer.sdp)
        }
    }

    pub async fn add_ice_candidate(&self, candidate: webrtc::ice_transport::ice_candidate::RTCIceCandidateInit) -> webrtc::error::Result<()> {
        self.peer_connection.add_ice_candidate(candidate).await
    }

    pub async fn send_video_packet(&self, buffer: Vec<u8>) -> webrtc::error::Result<()> {
        self.video_track.write_sample(&Sample {
            data: buffer.into(),
            duration: std::time::Duration::from_millis(33), // ~30 FPS
            ..Default::default()
        }).await
    }

    pub async fn is_data_channel_ready(&self) -> bool {
        let dc = self.data_channel.lock().await;
        if let Some(channel) = dc.as_ref() {
            // If buffered amount is too high (> 1MB), skip frames to avoid lag
            return channel.ready_state() == RTCDataChannelState::Open && channel.buffered_amount().await < 1024 * 1024;
        }
        false
    }

    pub async fn send_data(&self, buffer: Vec<u8>) -> webrtc::error::Result<()> {
        let dc = self.data_channel.lock().await;
        if let Some(channel) = dc.as_ref() {
            if channel.ready_state() == RTCDataChannelState::Open {
                let _ = channel.send(&buffer.into()).await;
            }
        }
        Ok(())
    }
}
