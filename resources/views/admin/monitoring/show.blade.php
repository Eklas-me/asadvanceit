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
            background: linear-gradient(to top, rgba(0, 0, 0, 0.8), transparent);
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
            z-index: 5;
        }

        .live-dot {
            width: 8px;
            height: 8px;
            background: white;
            border-radius: 50%;
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {
            0% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }

            100% {
                opacity: 1;
            }
        }

        .paused-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.6);
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
            border: 2px solid rgba(255, 255, 255, 0.3);
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
            <div class="glass-panel p-2">
                <div class="player-wrapper" id="playerWrapper">
                    <!-- Live Badge -->
                    <div class="live-badge" id="liveBadge">
                        <div class="live-dot"></div> LIVE
                    </div>

                    <!-- Video/Image Elements -->
                    <video id="remoteVideo" autoplay playsinline class="w-100 h-100"
                        style="display: none; object-fit: contain;"></video>
                    <img id="stream-image" src="" alt="Live Stream" class="img-fluid"
                        style="display: none; width: 100%; height: 100%; object-fit: contain;">

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

            <!-- Debug Log Card -->
            <div class="card bg-dark text-white border-secondary mt-4">
                <div class="card-header border-secondary d-flex justify-content-between align-items-center">
                    <span>WebRTC Debug Log</span>
                    <button class="btn btn-sm btn-outline-light"
                        onclick="document.getElementById('debug-log').innerHTML=''">Clear</button>
                </div>
                <div class="card-body"
                    style="height: 150px; overflow-y: auto; font-family: monospace; font-size: 0.8rem; background: #0a0a0f;"
                    id="debug-log">
                    <!-- Logs will appear here -->
                </div>
            </div>
        </div>

        <!-- Right Column: Stats & Controls -->
        <div class="col-lg-4">
            <div class="glass-panel p-4">
                <h5 class="text-white mb-4"><i class="fas fa-chart-pie me-2 text-primary"></i>System Stats</h5>

                <div class="stat-card">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">CPU Usage</span>
                        <span class="text-white fw-bold" id="cpu-stat">--%</span>
                    </div>
                    <div class="progress" style="height: 6px; background: rgba(255,255,255,0.1);">
                        <div class="progress-bar bg-primary" id="cpu-bar" role="progressbar" style="width: 0%"></div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Memory Usage</span>
                        <span class="text-white fw-bold" id="ram-stat">-- GB</span>
                    </div>
                    <div class="progress" style="height: 6px; background: rgba(255,255,255,0.1);">
                        <div class="progress-bar bg-info" id="ram-bar" role="progressbar" style="width: 0%"></div>
                    </div>
                </div>

                <div class="stat-card mt-4">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="flex-shrink-0">
                            <div class="bg-primary bg-opacity-10 p-3 rounded-circle"
                                style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-desktop text-primary"></i>
                            </div>
                        </div>
                        <div>
                            <h6 class="text-white mb-0" style="font-size: 0.9rem;">Device Info</h6>
                            <p class="text-muted small mb-0">{{ $device->computer_name }}</p>
                        </div>
                    </div>
                    <div class="pt-2 border-top border-secondary">
                        <small class="text-muted d-block mb-1">Hardware ID</small>
                        <div class="text-white-50 font-monospace" style="font-size: 0.75rem; word-break: break-all;">
                            {{ $device->hardware_id }}
                        </div>
                    </div>
                </div>

                <h5 class="text-white mb-3 mt-4"><i class="fas fa-gamepad me-2 text-primary"></i>Remote Actions</h5>

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
        const hwid = "{{ $device->hardware_id }}";
        const agentControlChannel = `device-control.${hwid}`;

        // Channel names for dual subscription
        const deviceChannelName = `agent-monitor.device.${hwid}`;
        const userChannelName = "{{ $device->user ? 'agent-monitor.user.' . $device->user->id : '' }}";

        // Element references helper
        const getEl = (id) => document.getElementById(id);

        let pc = null;
        let isPlaying = true;
        let activeDataChannel = null;

        function log(msg, type = 'info') {
            const logEl = document.getElementById('debug-log');
            if (!logEl) return;
            const color = type === 'error' ? 'text-danger' : (type === 'success' ? 'text-success' : 'text-primary');
            const time = new Date().toLocaleTimeString();
            logEl.innerHTML += `<div><span class="text-muted">[${time}]</span> <span class="${color}">${msg}</span></div>`;
            logEl.scrollTop = logEl.scrollHeight;
            console.log(`[${type.toUpperCase()}] ${msg}`);
        }

        async function startWebRTC() {
            log('Initializing WebRTC PeerConnection...', 'info');
            if (pc) {
                try { pc.close(); } catch (e) { }
            }

            pc = new RTCPeerConnection({
                iceServers: [{ urls: 'stun:stun.l.google.com:19302' }]
            });

            pc.oniceconnectionstatechange = () => {
                log(`ICE Connection State: ${pc.iceConnectionState}`, pc.iceConnectionState === 'failed' ? 'error' : 'info');
                document.getElementById('streamStatus').textContent = `State: ${pc.iceConnectionState}`;
            };

            pc.onicecandidate = (event) => {
                if (event.candidate) {
                    sendSignal({ type: 'candidate', candidate: event.candidate });
                }
            };

            pc.ondatachannel = (event) => {
                const dc = event.channel;
                log(`Incoming DataChannel received: ${dc.label}`, 'success');
                setupDataChannel(dc);
            };

            // Local fallback channel
            const localDc = pc.createDataChannel("video_init");
            setupDataChannel(localDc);

            try {
                const offer = await pc.createOffer({ offerToReceiveVideo: true });
                await pc.setLocalDescription(offer);
                log('Offer created. Sending to Agent...', 'info');
                sendSignal({ type: 'offer', sdp: offer.sdp });
            } catch (err) {
                log('Failed to create offer: ' + err.message, 'error');
            }
        }

        function setupDataChannel(dc) {
            dc.binaryType = 'arraybuffer';
            dc.onopen = () => {
                log(`DataChannel '${dc.label}' is now OPEN`, 'success');
                activeDataChannel = dc;
                document.getElementById('streamStatus').textContent = 'Live Streaming Ready';
            };

            dc.onmessage = (event) => {
                if (!isPlaying) return;
                
                const blob = new Blob([event.data], { type: 'image/jpeg' });
                const url = URL.createObjectURL(blob);
                
                const imgEl = getEl('stream-image');
                const loadingEl = getEl('loading-text');
                
                if (imgEl) {
                    imgEl.src = url;
                    imgEl.style.display = 'block';
                    imgEl.onload = () => URL.revokeObjectURL(url);
                }
                
                if (loadingEl) loadingEl.style.display = 'none';
                
                const videoEl = getEl('remoteVideo');
                if (videoEl) videoEl.style.display = 'none';
            };
        }

        async function sendSignal(payload, eventType = 'webrtc.signal') {
            try {
                await fetch('/api/agent/signal', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        payload: payload,
                        target_channel: agentControlChannel,
                        event_type: eventType
                    })
                });
            } catch (e) {
                log('Signal send failed: ' + e.message, 'error');
            }
        }

        window.togglePlay = function () {
            if (isPlaying) {
                isPlaying = false;
                document.getElementById('playerWrapper').classList.add('paused');
                document.getElementById('playIcon').className = 'fas fa-play';
                sendControlAction('stop_capture');
            } else {
                isPlaying = true;
                document.getElementById('playerWrapper').classList.remove('paused');
                document.getElementById('playIcon').className = 'fas fa-pause';
                sendControlAction('start_capture');
            }
        };

        window.sendControlAction = function (action) {
            log(`Sending Control Action: ${action}`, 'info');
            sendSignal({ action: action }, 'control');
        };

        window.toggleFullscreen = function () {
            const el = document.getElementById('playerWrapper');
            if (!document.fullscreenElement) {
                el.requestFullscreen();
            } else {
                document.exitFullscreen();
            }
        };

        function setupListeners(channel) {
            channel.listen('.agent.data', (e) => {
                if (e.stats) updateStats(e.stats);
                
                // Legacy Fallback
                const loadingEl = getEl('loading-text');
                const imgEl = getEl('stream-image');
                if (e.screenImage && loadingEl && loadingEl.style.display !== 'none') {
                    if (imgEl) {
                        imgEl.src = 'data:image/jpeg;base64,' + e.screenImage;
                        imgEl.style.display = 'block';
                    }
                    loadingEl.style.display = 'none';
                }
            });

            channel.listen('.webrtc.signal', async (data) => {
                log(`Received Signal: ${data.type}`, 'info');
                if (data.type === 'answer' && pc) {
                    try {
                        log('Setting Remote Description (Answer)...', 'info');
                        let sdp = data.sdp;
                        if (sdp) {
                            sdp = sdp.split('\n').map(l => l.trim()).join('\r\n') + '\r\n';
                        }
                        await pc.setRemoteDescription(new RTCSessionDescription({ type: 'answer', sdp: sdp }));
                    } catch (err) {
                        log('SDP Answer Error: ' + err.message, 'error');
                    }
                } else if (data.type === 'candidate' && pc) {
                    try {
                        await pc.addIceCandidate(new RTCIceCandidate(data.candidate));
                    } catch (err) {
                        log('ICE Candidate Error: ' + err.message, 'error');
                    }
                }
            });
        }

        const checkEcho = setInterval(() => {
            if (window.Echo) {
                clearInterval(checkEcho);
                log('Echo initialized successfully', 'success');

                // Subscribe to device channel (primary for signaling)
                log(`Subscribing to device channel: ${deviceChannelName}`, 'info');
                const deviceChannel = window.Echo.private(deviceChannelName);
                setupListeners(deviceChannel);

                // Subscribe to user channel if exists (often used for stats)
                if (userChannelName) {
                    log(`Subscribing to user channel: ${userChannelName}`, 'info');
                    const userChannel = window.Echo.private(userChannelName);
                    setupListeners(userChannel);
                }

                setTimeout(startWebRTC, 1000);
            }
        }, 500);

        function updateStats(stats) {
            const cpuStat = getEl('cpu-stat');
            const cpuBar = getEl('cpu-bar');
            const ramStat = getEl('ram-stat');
            const ramBar = getEl('ram-bar');

            if (stats.cpu !== undefined) {
                if (cpuStat) cpuStat.textContent = Math.round(stats.cpu) + '%';
                if (cpuBar) cpuBar.style.width = Math.round(stats.cpu) + '%';
            }
            if (stats.ram_used !== undefined && stats.ram_total !== undefined) {
                const usedGb = (stats.ram_used / (1024 ** 3)).toFixed(1);
                const totalGb = (stats.ram_total / (1024 ** 3)).toFixed(1);
                if (ramStat) ramStat.textContent = `${usedGb} / ${totalGb} GB`;
                if (ramBar) ramBar.style.width = (stats.ram_used / stats.ram_total * 100) + '%';
            }
        }
    </script>
@endpush