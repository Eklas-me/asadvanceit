@extends('layouts.dashboard')

@push('styles')
<style>
    .glass-player-container {
        background: rgba(20, 20, 30, 0.95);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.05);
        border-radius: 30px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        position: relative;
        overflow: hidden;
        margin-top: 1rem;
    }
    .player-header {
        padding: 25px 35px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .stream-wrapper {
        position: relative;
        background: #000;
        min-height: 500px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .stats-tray {
        padding: 25px 35px;
        background: rgba(255, 255, 255, 0.02);
        display: flex;
        gap: 30px;
        flex-wrap: wrap;
    }
    .stat-item {
        background: rgba(255, 255, 255, 0.03);
        padding: 15px 25px;
        border-radius: 15px;
        border: 1px solid rgba(255, 255, 255, 0.05);
        flex: 1;
        min-width: 150px;
    }
    .stat-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: #fff;
    }
    .stat-label {
        color: #888;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    .live-indicator {
        display: flex;
        align-items: center;
        gap: 8px;
        background: rgba(220, 53, 69, 0.1);
        padding: 6px 12px;
        border-radius: 20px;
        color: #dc3545;
        font-weight: 700;
        font-size: 0.75rem;
    }
    .live-dot {
        width: 8px;
        height: 8px;
        background: #dc3545;
        border-radius: 50%;
        animation: pulse 1.5s infinite;
    }
    @keyframes pulse {
        0% { transform: scale(1); opacity: 1; }
        50% { transform: scale(1.5); opacity: 0.5; }
        100% { transform: scale(1); opacity: 1; }
    }
    .premium-title {
        font-weight: 800;
        background: linear-gradient(to right, #fff, #999);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
</style>
@endpush

@section('content')
    <div class="glass-player-container fade-in">
        <div class="player-header">
            <div>
                <h2 class="premium-title mb-0">
                    {{ $device->user ? $device->user->name : $device->computer_name }}
                </h2>
                <p class="text-muted small mb-0">
                    @if($device->user)
                        <i class="fas fa-user-circle me-1"></i> {{ $device->user->email }}
                    @else
                        <i class="fas fa-desktop me-1"></i> {{ $device->computer_name }} (Agent Service)
                    @endif
                </p>
            </div>
            <div class="live-indicator">
                <div class="live-dot"></div>
                LIVE MONITORING
            </div>
        </div>

        <div class="stream-wrapper">
            <video id="remoteVideo" autoplay playsinline class="w-100 h-100" style="display: none; background: #000;"></video>
            <img id="stream-image" src="" alt="Live Stream" class="img-fluid" style="display: none; width: 100%;">
            <div id="loading-text" class="text-center p-5">
                <div class="spinner-border text-primary mb-3" role="status"></div>
                <p class="text-muted">Establishing secure WebRTC connection...</p>
            </div>
        </div>

        <div class="stats-tray">
            <div class="stat-item">
                <div class="stat-label">CPU Usage</div>
                <div class="stat-value" id="cpu-stat">--%</div>
            </div>
            <div class="stat-item">
                <div class="stat-label">Memory</div>
                <div class="stat-value" id="ram-stat">-- GB</div>
            </div>
            <div class="stat-item">
                <div class="stat-label">Hardware ID</div>
                <div class="stat-value" style="font-size: 0.85rem; font-family: monospace; color: #aaa;">{{ $device->hardware_id }}</div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script type="module">
        const channelId = "{{ $device->user ? 'user.' . $device->user->id : 'device.' . $device->hardware_id }}";
        const hwid = "{{ $device->hardware_id }}";
        const agentControlChannel = `device-control.${hwid}`;

        document.addEventListener('DOMContentLoaded', () => {
            const videoEl = document.getElementById('remoteVideo');
            const imgEl = document.getElementById('stream-image');
            const loadingEl = document.getElementById('loading-text');

            let pc = null;

            async function startWebRTC() {
                console.log('Starting WebRTC connection...');
                pc = new RTCPeerConnection({
                    iceServers: [{ urls: 'stun:stun.l.google.com:19302' }]
                });

                pc.onicecandidate = (event) => {
                    if (event.candidate) {
                        sendSignal({ type: 'candidate', candidate: event.candidate });
                    }
                };

                pc.ontrack = (event) => {
                    console.log('Received remote track');
                    videoEl.srcObject = event.streams[0];
                    videoEl.style.display = 'block';
                    loadingEl.style.display = 'none';
                    imgEl.style.display = 'none';
                };

                const offer = await pc.createOffer({
                    offerToReceiveVideo: true,
                    offerToReceiveAudio: false
                });
                await pc.setLocalDescription(offer);

                sendSignal({ type: 'offer', sdp: offer.sdp });
            }

            async function sendSignal(payload) {
                try {
                    await fetch('/api/agent/signal', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            payload: payload,
                            target_channel: agentControlChannel
                        })
                    });
                } catch (e) {
                    console.error('Signal failed', e);
                }
            }

            const initEcho = setInterval(() => {
                if (window.Echo) {
                    clearInterval(initEcho);
                    console.log('Echo initialized. Subscribing to: ' + channelId);

                    const channel = window.Echo.private(`agent-monitor.${channelId}`);
                    
                    // Legacy Image Stream (Fallback)
                    channel.listen('.agent.data', (e) => {
                        if (e.screenImage && videoEl.style.display === 'none') {
                            imgEl.src = 'data:image/jpeg;base64,' + e.screenImage;
                            imgEl.style.display = 'block';
                            loadingEl.style.display = 'none';
                        }
                        updateStats(e.stats);
                    });

                    // WebRTC Signaling
                    channel.listen('.webrtc.signal', async (data) => {
                        console.log('Received signal from agent:', data);
                        if (!pc) return;

                        if (data.type === 'answer') {
                            await pc.setRemoteDescription(new RTCSessionDescription(data));
                        } else if (data.type === 'candidate' && data.candidate) {
                            await pc.addIceCandidate(new RTCIceCandidate(data.candidate));
                        }
                    });

                    // Start WebRTC handover
                    startWebRTC();
                }
            }, 500);

            function updateStats(stats) {
                if (!stats) return;
                if (stats.cpu !== undefined) {
                    document.getElementById('cpu-stat').textContent = Math.round(stats.cpu) + '%';
                }
                if (stats.ram_used !== undefined && stats.ram_total !== undefined) {
                    const usedGb = (stats.ram_used / (1024 * 1024 * 1024)).toFixed(1);
                    const totalGb = (stats.ram_total / (1024 * 1024 * 1024)).toFixed(1);
                    document.getElementById('ram-stat').textContent = `${usedGb} / ${totalGb} GB`;
                }
            }
        });
    </script>
@endpush
