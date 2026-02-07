use std::sync::Arc;
use webrtc::api::media_engine::MediaEngine;
use webrtc::api::APIBuilder;
use webrtc::peer_connection::configuration::RTCConfiguration;
use webrtc::peer_connection::RTCPeerConnection;
use webrtc::track::track_local::track_local_static_sample::TrackLocalStaticSample;
use webrtc::track::track_local::TrackLocal;
use webrtc::media::Sample;

pub struct WebRTCManager {
    peer_connection: Arc<RTCPeerConnection>,
    video_track: Arc<TrackLocalStaticSample>,
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
        })
    }

    pub async fn handle_offer(&self, sdp: &str) -> webrtc::error::Result<String> {
        let mut desc = webrtc::peer_connection::sdp::session_description::RTCSessionDescription::default();
        desc.sdp = sdp.to_owned();
        desc.sdp_type = webrtc::peer_connection::sdp::sdp_type::RTCSdpType::Offer;

        self.peer_connection.set_remote_description(desc).await?;
        
        let answer = self.peer_connection.create_answer(None).await?;
        self.peer_connection.set_local_description(answer.clone()).await?;
        
        Ok(answer.sdp)
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
}
