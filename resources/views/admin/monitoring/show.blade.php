@extends('layouts.dashboard')

@push('styles')
<style>
    .glass-panel {
        background: rgba(20, 20, 30, 0.8);
        backdrop-filter: blur(15px);
        border: 1px solid rgba(255, 255, 255, 0.05);
        border-radius: 24px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
        overflow: hidden;
        height: 100%;
    }
    .player-wrapper {
        position: relative;
        background: #000;
        border-radius: 24px;
        overflow: hidden;
        aspect-ratio: 16/9;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }
    .stream-overlay {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        padding: 20px;
        background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);
        opacity: 0;
        transition: opacity 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .player-wrapper:hover .stream-overlay {
        opacity: 1;
    }
    .play-pause-btn {
        background: rgba(255, 255, 255, 0.2);
        border: none;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        backdrop-filter: blur(5px);
        transition: all 0.2s;
    }
    .play-pause-btn:hover {
        background: rgba(255, 255, 255, 0.4);
        transform: scale(1.1);
    }
    .stat-card {
        background: rgba(255, 255, 255, 0.03);
        border-radius: 16px;
        padding: 20px;
        margin-bottom: 15px;
        border: 1px solid rgba(255, 255, 255, 0.05);
    }
    .control-btn {
        width: 100%;
        padding: 12px;
        border-radius: 12px;
        border: none;
        font-weight: 600;
        margin-bottom: 10px;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        cursor: pointer;
    }
    .btn-power {
        background: rgba(220, 53, 69, 0.15);
        color: #ff6b6b;
        border: 1px solid rgba(220, 53, 69, 0.2);
    }
    .btn-power:hover {
        background: rgba(220, 53, 69, 0.3);
    }
    .btn-sleep {
        background: rgba(255, 193, 7, 0.15);
        color: #ffc107;
        border: 1px solid rgba(255, 193, 7, 0.2);
    }
    .btn-sleep:hover {
        background: rgba(255, 193, 7, 0.3);
    }
    .live-badge {
        position: absolute;
        top: 20px;
        right: 20px;
        background: rgba(220, 53, 69, 0.9);
        color: white;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: bold;
        display: flex;
        align-items: center;
        gap: 6px;
        backdrop-filter: blur(4px);
    }
    .live-dot {
        width: 8px;
        height: 8px;
        background: white;
        border-radius: 50%;
        animation: pulse 1.5s infinite;
    }
    @keyframes pulse {
        0% { opacity: 1; }
        50% { opacity: 0.5; }
        100% { opacity: 1; }
    }
    .paused-overlay {
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(0,0,0,0.6);
        backdrop-filter: blur(5px);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        z-index: 10;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.3s;
    }
    .player-wrapper.paused .paused-overlay {
        opacity: 1;
        pointer-events: auto;
    }
    .big-play-btn {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(10px);
        border: 2px solid rgba(255,255,255,0.3);
        color: white;
        font-size: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: transform 0.2s;
    }
    .big-play-btn:hover {
        transform: scale(1.1);
        background: rgba(255, 255, 255, 0.3);
    }
</style>
@endpush

@section('content')
<div class="row g-4 fade-in">
    <!-- Left Column: Video Player -->
    <div class="col-lg-8">
        <div class="player-wrapper" id="playerWrapper">
            <!-- Live Badge -->
            <div class="live-badge" id="liveBadge">
                <div class="live-dot"></div> LIVE
            </div>

            <!-- Video/Image Elements -->
            <video id="remoteVideo" autoplay playsinline class="w-100 h-100" style="display: none; object-fit: contain;"></video>
            <img id="stream-image" src="" alt="Live Stream" class="img-fluid" style="display: none; width: 100%; height: 100%; object-fit: contain;">
            
            <!-- Loading State -->
            <div id="loading-text" class="text-center text-white">
                <div class="spinner-border text-primary mb-3" role="status"></div>
                <p class="text-white-50">Connecting to device...</p>
            </div>

            <!-- Paused Overlay -->
            <div class="paused-overlay">
                <div class="big-play-btn" onclick="togglePlay()">
                    <i class="fas fa-play"></i>
                </div>
                <p class="mt-3 text-white fw-bold">Stream Paused</p>
                <small class="text-white-50">Click to resume</small>
            </div>

            <!-- Controls Overlay -->
            <div class="stream-overlay">
                <div class="d-flex align-items-center gap-3">
                    <button class="play-pause-btn" onclick="togglePlay()">
                        <i class="fas fa-pause" id="playIcon"></i>
                    </button>
                    <div>
                        <h6 class="text-white mb-0">{{ $device->computer_name }}</h6>
                        <small class="text-white-50" id="streamStatus">Connected via WebRTC</small>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <button class="play-pause-btn" onclick="toggleFullscreen()" title="Fullscreen">
                        <i class="fas fa-expand"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Stats & Controls -->
    <div class="col-lg-4">
        <div class="glass-panel p-4">
            <h5 class="text-white mb-4"><i class="fas fa-chart-pie me-2 text-primary"></i>System Stats</h5>
            
            <div class="stat-card">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">CPU Usage</span>
                    <span class="text-white fw-bold" id="cpu-stat">--%</span>
                </div>
                <div class="progress" style="height: 6px; background: rgba(255,255,255,0.1);">
                    <div class="progress-bar bg-primary" id="cpu-bar" role="progressbar" style="width: 0%"></div>
                </div>
            </div>

            <div class="stat-card">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Memory Usage</span>
                    <span class="text-white fw-bold" id="ram-stat">-- GB</span>
                </div>
                <div class="progress" style="height: 6px; background: rgba(255,255,255,0.1);">
                    <div class="progress-bar bg-info" id="ram-bar" role="progressbar" style="width: 0%"></div>
                </div>
            </div>

            <div class="stat-card">
                <small class="text-muted d-block mb-1">Hardware ID</small>
                <div class="text-white-50 font-monospace" style="font-size: 0.85rem;">{{ $device->hardware_id }}</div>
            </div>

            <h5 class="text-white mb-3 mt-4"><i class="fas fa-gamepad me-2 text-primary"></i>Actions</h5>
            
            <button class="control-btn btn-power" onclick="sendControlAction('power')">
                <i class="fas fa-power-off"></i> Power Off
            </button>
            <button class="control-btn btn-sleep" onclick="sendControlAction('sleep')">
                <i class="fas fa-moon"></i> Sleep Mode
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script type="module">
        const channelId = "{{ $device->user ? 'user.' . $device->user->id : 'device.' . $device->hardware_id }}";
        const hwid = "{{ $device->hardware_id }}";
        const agentControlChannel = `device-control.${hwid}`;
        
        let pc = null;
        let isPlaying = true;
        let connectionActive = true;

        // Auto-Stop when tab is hidden
        document.addEventListener("visibilitychange", () => {
            if (document.hidden) {
                console.log("Tab hidden, pausing stream...");
                pauseStream();
            } else {
                console.log("Tab visible, checking auto-resume...");
                // Optional: Auto-resume or let user click play
                // pauseStream() sets isPlaying=false, so user must click play manually usually
                // But for UX, we might want to auto-resume if it was playing before.
                // For now, let's just stop it to save resources as requested.
            }
        });

        // Cleanup on unload
        window.addEventListener('beforeunload', () => {
            sendControlAction('stop_capture'); // Use the new function
            if (pc) pc.close();
        });

        window.togglePlay = function() {
            if (isPlaying) {
                pauseStream();
            } else {
                playStream();
            }
        }

        function pauseStream() {
            isPlaying = false;
            document.getElementById('playerWrapper').classList.add('paused');
            document.getElementById('playIcon').classList.remove('fa-pause');
            document.getElementById('playIcon').classList.add('fa-play');
            document.getElementById('liveBadge').style.opacity = '0.5';
            
            // Send Signal to Agent to Stop Capturing
            sendControlAction('stop_capture');
        }

        function playStream() {
            isPlaying = true;
            document.getElementById('playerWrapper').classList.remove('paused');
            document.getElementById('playIcon').classList.remove('fa-play');
            document.getElementById('playIcon').classList.add('fa-pause');
            document.getElementById('liveBadge').style.opacity = '1';

            // Send Signal to Agent to Start Capturing
            sendControlAction('start_capture');
        }

        window.toggleFullscreen = function() {
            const elem = document.getElementById("playerWrapper");
            if (!document.fullscreenElement) {
                elem.requestFullscreen().catch(err => {
                    alert(`Error attempting to enable fullscreen: ${err.message}`);
                });
            } else {
                document.exitFullscreen();
            }
        }

        window.sendControlAction = function(action) {
            console.log('Sending Control Action:', action);
            if(action === 'power' || action === 'sleep') {
                if(!confirm(`Are you sure you want to ${action} the remote device?`)) return;
            }
            sendSignal({ action: action }, 'control');
        }

        async function sendSignal(payload, type = 'webrtc') {
            try {
                // Determine event type based on payload
                let event = 'webrtc.signal'; // default
                if (type === 'control') event = 'control'; // specialized control event if backend supports or just wrap in payload

                // Actually, the current backend might expect a specific structure.
                // Let's stick to the structure Agent expects.
                // Agent main.rs expects: if event == "control"
                
                // We need to change the API endpoint or payload to make sure Reverb sends "event": "control"
                // OR we can wrap it in "webrtc.signal" but change the "type" inside dispatch.
                // Wait, Agent main.rs checks: if event == "control"
                // So we need to trigger a 'control' event on the channel.
                
                // Since we are using a generic /api/agent/signal endpoint which likely broadcasts "webrtc.signal"
                // We might need to update the backend route to support custom events OR update Agent to check inside "webrtc.signal".
                
                // Let's Look at Agent Code:
                // if event == "webrtc.signal" { ... }
                // } else if event == "control" { ... }
                
                // So we MUST send an event named "control".
                // I will add a 'type' param to the fetch call if possible, or just add logic to the Controller.
                
                await fetch('/api/agent/signal', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        payload: payload,
                        target_channel: agentControlChannel,
                        event_type: type // We need to support this in backend or Agent
                    })
                });
            } catch (e) {
                console.error('Signal failed', e);
            }
        }

        // ... WebRTC Logic ...
        document.addEventListener('DOMContentLoaded', () => {
            const videoEl = document.getElementById('remoteVideo');
            const imgEl = document.getElementById('stream-image');
            const loadingEl = document.getElementById('loading-text');

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

                pc.ondatachannel = (event) => {
                    const receiveChannel = event.channel;
                    receiveChannel.onmessage = (e) => {
                        if (!isPlaying) return; // Ignore frames if paused
                        const blob = new Blob([e.data], { type: 'image/jpeg' });
                        const url = URL.createObjectURL(blob);
                        imgEl.src = url;
                        imgEl.style.display = 'block';
                        loadingEl.style.display = 'none';
                        imgEl.onload = () => URL.revokeObjectURL(url);
                    };
                };

                const offer = await pc.createOffer({ offerToReceiveVideo: true });
                await pc.setLocalDescription(offer);
                sendSignal({ type: 'offer', sdp: offer.sdp });
            }

            const initEcho = setInterval(() => {
                if (window.Echo) {
                    clearInterval(initEcho);
                    const channel = window.Echo.private(`agent-monitor.${channelId}`);
                    
                    channel.listen('.agent.data', (e) => {
                        updateStats(e.stats);
                    });

                    channel.listen('.webrtc.signal', async (data) => {
                        if (!pc) return;
                        if (data.type === 'answer') {
                            await pc.setRemoteDescription(new RTCSessionDescription(data));
                        } else if (data.type === 'candidate' && data.candidate) {
                            await pc.addIceCandidate(new RTCIceCandidate(data.candidate));
                        }
                    });

                    startWebRTC();
                }
            }, 500);

            function updateStats(stats) {
                if (!stats) return;
                if (stats.cpu !== undefined) {
                    document.getElementById('cpu-stat').textContent = Math.round(stats.cpu) + '%';
                    document.getElementById('cpu-bar').style.width = Math.round(stats.cpu) + '%';
                }
                if (stats.ram_used !== undefined && stats.ram_total !== undefined) {
                    const usedGb = (stats.ram_used / (1024 * 1024 * 1024)).toFixed(1);
                    const totalGb = (stats.ram_total / (1024 * 1024 * 1024)).toFixed(1);
                    const percent = (stats.ram_used / stats.ram_total) * 100;
                    document.getElementById('ram-stat').textContent = `${usedGb} / ${totalGb} GB`;
                    document.getElementById('ram-bar').style.width = percent + '%';
                }
            }
        });
    </script>
@endpush
