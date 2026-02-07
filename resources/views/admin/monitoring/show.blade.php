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
        <div class="col-lg-8">
            <div class="player-wrapper" id="playerWrapper">
                <div class="live-badge" id="liveBadge">
                    <div class="live-dot"></div> LIVE
                </div>

                <video id="remoteVideo" autoplay playsinline class="w-100 h-100" style="display: none; object-fit: contain;"></video>
                <img id="stream-image" src="" alt="Live Stream" class="img-fluid" style="display: none; width: 100%; height: 100%; object-fit: contain;">

                <div id="loading-text" class="text-center text-white">
                    <div class="spinner-border text-primary mb-3" role="status"></div>
                    <p class="text-white-50">Connecting to device...</p>
                </div>

                <div class="paused-overlay">
                    <div class="big-play-btn" onclick="togglePlay()">
                        <i class="fas fa-play"></i>
                    </div>
                    <p class="mt-3 text-white fw-bold">Stream Paused</p>
                    <small class="text-white-50">Click to resume</small>
                </div>

                <div class="stream-overlay">
                    <div class="d-flex align-items-center gap-3">
                        <button class="play-pause-btn" onclick="togglePlay()">
                            <i class="fas fa-pause" id="playIcon"></i>
                        </button>
                        <div>
                            <h6 class="text-white mb-0">{{ $device->computer_name }}</h6>
                            <small class="text-white-50" id="streamStatus">Connecting...</small>
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

    <div class="row mt-4">
        <div class="col-12">
            <div class="card bg-dark text-white border-secondary">
                <div class="card-header border-secondary d-flex justify-content-between align-items-center">
                    <span>WebRTC Debug Log</span>
                    <button class="btn btn-sm btn-outline-light" onclick="document.getElementById('debug-log').innerHTML=''">Clear</button>
                </div>
                <div class="card-body" style="height: 250px; overflow-y: auto; font-family: monospace; font-size: 0.85rem; background: #0a0a0f;" id="debug-log"></div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script type="module">
        const hwid = "{{ $device->hardware_id }}";
        const agentControlChannel = `device-control.${hwid}`;
        
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
            if (pc) pc.close();

            pc = new RTCPeerConnection({
                iceServers: [{ urls: 'stun:stun.l.google.com:19302' }]
            });

            pc.oniceconnectionstatechange = () => {
                log(`ICE Connection State: ${pc.iceConnectionState}`, pc.iceConnectionState === 'failed' ? 'error' : 'info');
                document.getElementById('streamStatus').textContent = `State: ${pc.iceConnectionState}`;
            };

            pc.onconnectionstatechange = () => {
                log(`Peer Connection State: ${pc.connectionState}`, pc.connectionState === 'failed' ? 'error' : 'info');
            };

            pc.onicecandidate = (event) => {
                if (event.candidate) {
                    sendSignal({ type: 'candidate', candidate: event.candidate });
                }
            };

            // Handle DataChannels (especially Agent-initiated ones)
            pc.ondatachannel = (event) => {
                const dc = event.channel;
                log(`Incoming DataChannel received: ${dc.label}`, 'success');
                setupDataChannel(dc);
            };

            // Create an initial channel to ensure SCTP negotiation starts
            const localDc = pc.createDataChannel("video_init");
            setupDataChannel(localDc);

            try {
                const offer = await pc.createOffer({ offerToReceiveVideo: true });
                await pc.setLocalDescription(offer);
                log('WebRTC Offer created and set. Sending to Agent...', 'info');
                sendSignal({ type: 'offer', sdp: offer.sdp });
            } catch (err) {
                log('Failed to create offer: ' + err.message, 'error');
            }
        }

        function setupDataChannel(dc) {
            dc.binaryType = 'arraybuffer';
            dc.onopen = () => {
                log(`DataChannel '${dc.label}' is now OPEN`, 'success');
                if (dc.label === 'video' || dc.label === 'video_init') {
                    activeDataChannel = dc;
                    document.getElementById('streamStatus').textContent = 'Live Streaming Ready';
                }
            };
            dc.onclose = () => log(`DataChannel '${dc.label}' closed`, 'error');
            dc.onerror = (e) => log(`DataChannel '${dc.label}' error: ${e.message || 'Unknown'}`, 'error');
            
            dc.onmessage = (event) => {
                if (!isPlaying) return;
                
                const blob = new Blob([event.data], { type: 'image/jpeg' });
                const url = URL.createObjectURL(blob);
                
                const imgEl = document.getElementById('stream-image');
                const loadingEl = document.getElementById('loading-text');
                
                imgEl.src = url;
                imgEl.style.display = 'block';
                loadingEl.style.display = 'none';
                
                imgEl.onload = () => URL.revokeObjectURL(url);
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

        window.togglePlay = function() {
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

        window.sendControlAction = function(action) {
            log(`Sending Control Action: ${action}`, 'info');
            sendSignal({ action: action }, 'control');
        };

        window.toggleFullscreen = function() {
            const el = document.getElementById('playerWrapper');
            if (!document.fullscreenElement) {
                el.requestFullscreen();
            } else {
                document.exitFullscreen();
            }
        };

        // Initialize Echo and Listeners
        const checkEcho = setInterval(() => {
            if (window.Echo) {
                clearInterval(checkEcho);
                log('Pusher/Echo initialized successfully', 'success');
                
                const channel = window.Echo.private(`agent-monitor.device.${hwid}`);
                
                channel.listen('.agent.data', (e) => {
                    if (e.stats) updateStats(e.stats);
                });

                channel.listen('.webrtc.signal', async (data) => {
                    log(`Received Signal: ${data.type}`, 'info');
                    if (data.type === 'answer' && pc) {
                        try {
                            log('Setting Remote Description (Answer)...', 'info');
                            
                            // Robust SDP fix for chrome/webrtc-rs compatibility
                            let sdp = data.sdp;
                            if (sdp) {
                                // Ensure CRLF and strip extra whitespace
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

                // Start WebRTC connection
                setTimeout(startWebRTC, 1000);
            }
        }, 500);

        function updateStats(stats) {
            if (stats.cpu !== undefined) {
                document.getElementById('cpu-stat').textContent = Math.round(stats.cpu) + '%';
                document.getElementById('cpu-bar').style.width = Math.round(stats.cpu) + '%';
            }
            if (stats.ram_used !== undefined && stats.ram_total !== undefined) {
                const usedGb = (stats.ram_used / (1024 ** 3)).toFixed(1);
                const totalGb = (stats.ram_total / (1024 ** 3)).toFixed(1);
                document.getElementById('ram-stat').textContent = `${usedGb} / ${totalGb} GB`;
                document.getElementById('ram-bar').style.width = (stats.ram_used / stats.ram_total * 100) + '%';
            }
        }
    </script>
@endpush
