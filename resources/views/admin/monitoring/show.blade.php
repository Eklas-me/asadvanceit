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
                            <!-- Smoothness Toggle -->
                            <div class="form-check form-switch me-3">
                                <input class="form-check-input" type="checkbox" id="smoothToggle"
                                    onchange="toggleSmoothMode()" checked>
                                <label class="form-check-label text-white-50 small" for="smoothToggle">Smooth Mode</label>
                            </div>
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
        // 1. Data & Channels
        const hwid = "{{ $device->hardware_id }}";
        const userId = "{{ $device->user_id }}";
        const deviceChannelId = `agent-monitor.device.${hwid}`;
        const userChannelId = userId ? `agent-monitor.user.${userId}` : null;
        const controlChannel = `device-control.${hwid}`;

        // 2. State & Elements
        let pc = null;
        let isPlaying = true;
        const getEl = (id) => document.getElementById(id);

        function log(msg, type = 'info') {
            const logEl = getEl('debug-log');
            if (logEl) {
                const time = new Date().toLocaleTimeString();
                const color = type === 'error' ? 'text-danger' : (type === 'success' ? 'text-success' : 'text-primary');
                logEl.innerHTML += `<div><span class="text-muted">[${time}]</span> <span class="${color}">${msg}</span></div>`;
                logEl.scrollTop = logEl.scrollHeight;
            }
            console.log(`[${type.toUpperCase()}] ${msg}`);
        }

        // 3. WebRTC Logic
        async function initWebRTC() {
            log('Starting WebRTC Initialization...', 'info');
            if (pc) { try { pc.close(); } catch (e) { } }

            pc = new RTCPeerConnection({
                iceServers: [{ urls: 'stun:stun.l.google.com:19302' }]
            });

            pc.onicecandidate = (e) => {
                if (e.candidate) sendSignal({ type: 'candidate', candidate: e.candidate });
            };

            pc.oniceconnectionstatechange = () => {
                log(`Connection State: ${pc.iceConnectionState}`, 'info');
                const statusEl = getEl('streamStatus');
                if (statusEl) statusEl.textContent = `Status: ${pc.iceConnectionState}`;
            };

            pc.ontrack = (e) => {
                log('Remote Track Received', 'success');
                const v = getEl('remoteVideo');
                if (v) {
                    v.srcObject = e.streams[0];
                    v.style.display = 'block';
                    v.onplaying = () => {
                        log('Video Track Playing', 'success');
                        lastWebRTCFrame = Date.now();
                        const i = getEl('stream-image');
                        if (i) i.style.display = 'none';
                        const l = getEl('loading-text');
                        if (l) l.style.display = 'none';
                    };
                    v.ontimeupdate = () => {
                        lastWebRTCFrame = Date.now();
                    };
                }

                // Apply initial buffering if enabled
                applyBuffering(e.receiver);
            };

            pc.ondatachannel = (e) => {
                log('DataChannel Received: ' + e.channel.label, 'success');
                setupDC(e.channel);
            };

            const dc = pc.createDataChannel("video_init");
            setupDC(dc);

            try {
                const offer = await pc.createOffer({ offerToReceiveVideo: true });
                await pc.setLocalDescription(offer);
                sendSignal({ type: 'offer', sdp: offer.sdp });
            } catch (err) {
                log('Offer Error: ' + err.message, 'error');
            }
        }

        // 4. Stream Prioritization
        let lastWebRTCFrame = 0;
        const WEBRTC_TIMEOUT = 5000; // Increased timeout for smooth mode

        function setupDC(dc) {
            dc.binaryType = 'arraybuffer';
            dc.onmessage = (e) => {
                if (!isPlaying) return;
                lastWebRTCFrame = Date.now();

                const blob = new Blob([e.data], { type: 'image/jpeg' });
                const url = URL.createObjectURL(blob);
                const i = getEl('stream-image');
                if (i) {
                    i.src = url;
                    // Only show if video isn't active
                    const v = getEl('remoteVideo');
                    if (v && (!v.srcObject || v.paused)) {
                        i.style.display = 'block';
                    }
                    i.onload = () => URL.revokeObjectURL(url);
                }
                const l = getEl('loading-text');
                if (l) l.style.display = 'none';
            };
            dc.onclose = () => log('DataChannel Closed', 'error');
            dc.onerror = (err) => log('DataChannel Error: ' + err.message, 'error');
        }

        async function sendSignal(payload, eventType = 'webrtc.signal') {
            try {
                await fetch('/api/agent/signal', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ payload, target_channel: controlChannel, event_type: eventType })
                });
            } catch (e) { log('Signal Error: ' + e.message, 'error'); }
        }

        function setupEchoListeners(channel, name) {
            log(`Listening on channel: ${name}`, 'info');
            channel.listen('.agent.data', (e) => {
                if (e.stats) updateUIStats(e.stats);

                // Smart Fallback Logic
                // Only update from legacy stream if WebRTC has NOT sent a frame recently
                const now = Date.now();
                const isWebRTCAlive = (now - lastWebRTCFrame < WEBRTC_TIMEOUT);
                
                if (e.screenImage && !isWebRTCAlive) {
                    const i = getEl('stream-image');
                    const l = getEl('loading-text');
                    const v = getEl('remoteVideo');

                    if (i) {
                        i.src = 'data:image/jpeg;base64,' + e.screenImage;
                        i.style.display = 'block';
                    }
                    if (l) l.style.display = 'none';
                    if (v) v.style.display = 'none';
                }
            });

            channel.listen('.webrtc.signal', async (data) => {
                log(`Signal Received: ${data.type}`, 'info');
                if (!pc) return;
                if (data.type === 'answer') {
                    try {
                        let sdp = data.sdp;
                        if (sdp && !sdp.includes('\r\n')) sdp = sdp.split('\n').join('\r\n');
                        await pc.setRemoteDescription(new RTCSessionDescription({ type: 'answer', sdp }));
                    } catch (err) { log('SDP Error: ' + err.message, 'error'); }
                } else if (data.type === 'candidate' && data.candidate) {
                    try { await pc.addIceCandidate(new RTCIceCandidate(data.candidate)); } catch (e) { }
                }
            });
        }

        const waitEcho = setInterval(() => {
            if (window.Echo) {
                clearInterval(waitEcho);
                log('Echo Connected', 'success');
                const chan1 = window.Echo.private(deviceChannelId);
                setupEchoListeners(chan1, deviceChannelId);
                if (userChannelId && userChannelId !== deviceChannelId) {
                    const chan2 = window.Echo.private(userChannelId);
                    setupEchoListeners(chan2, userChannelId);
                }
                setTimeout(initWebRTC, 1000);
            }
        }, 500);

        function updateUIStats(stats) {
            const cs = getEl('cpu-stat'), cb = getEl('cpu-bar'), rs = getEl('ram-stat'), rb = getEl('ram-bar');
            if (stats.cpu !== undefined) {
                if (cs) cs.textContent = Math.round(stats.cpu) + '%';
                if (cb) cb.style.width = Math.round(stats.cpu) + '%';
            }
            if (stats.ram_used !== undefined && stats.ram_total !== undefined) {
                const u = (stats.ram_used / (1024 ** 3)).toFixed(1), t = (stats.ram_total / (1024 ** 3)).toFixed(1);
                if (rs) rs.textContent = `${u} / ${t} GB`;
                if (rb) rb.style.width = (stats.ram_used / stats.ram_total * 100) + '%';
            }
        }

        window.togglePlay = () => {
            isPlaying = !isPlaying;
            document.getElementById('playerWrapper').classList.toggle('paused', !isPlaying);
            document.getElementById('playIcon').className = isPlaying ? 'fas fa-pause' : 'fas fa-play';
            sendControlAction(isPlaying ? 'start_capture' : 'stop_capture');
        };

        window.sendControlAction = (action) => {
            log(`Action: ${action}`, 'info');
            sendSignal({ action: action }, 'control');
        };

        window.toggleFullscreen = () => {
            const p = document.getElementById('playerWrapper');
            if (!document.fullscreenElement) p.requestFullscreen();
            else document.exitFullscreen();
        };

        window.toggleSmoothMode = () => {
            const isSmooth = document.getElementById('smoothToggle').checked;
            log(`Smooth Mode: ${isSmooth ? 'ENABLED (10s Buffer)' : 'DISABLED (Real-time)'}`, 'info');

            if (pc) {
                pc.getReceivers().forEach(receiver => {
                    applyBuffering(receiver);
                });
            }

            const statusEl = getEl('streamStatus');
            if (statusEl) {
                statusEl.textContent = isSmooth ? 'Connected (Smooth Mode - 10s Delay)' : 'Connected (Real-time Mode)';
            }
        };

        function applyBuffering(receiver) {
            if (receiver.track && receiver.track.kind === 'video') {
                const isSmooth = document.getElementById('smoothToggle').checked;
                // playoutDelayHint is supported in Chrome/Edge to introduce artificial delay
                // Value is in seconds.
                if ('playoutDelayHint' in receiver) {
                    receiver.playoutDelayHint = isSmooth ? 10.0 : 0.0;
                    log(`Jitter Buffer set to: ${receiver.playoutDelayHint}s`, 'success');
                } else {
                    log('playoutDelayHint not supported in this browser', 'warning');
                }
            }
        }
    </script>
@endpush