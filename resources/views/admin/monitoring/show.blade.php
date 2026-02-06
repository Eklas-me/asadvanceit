@extends('layouts.app')

@push('styles')
    <style>
        .monitor-screen {
            width: 100%;
            max-width: 1000px;
            margin: 0 auto;
            background: #000;
            border: 2px solid #333;
            border-radius: 8px;
            overflow: hidden;
            aspect-ratio: 16/9;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        #stream-image {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .stats-card {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 8px;
            padding: 1rem;
            margin-top: 1rem;
        }

        .stat-label {
            font-size: 0.8rem;
            color: #aaa;
            text-transform: uppercase;
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: bold;
            color: #fff;
        }

        .live-indicator {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(0, 0, 0, 0.6);
            color: #ef4444;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .dot {
            width: 8px;
            height: 8px;
            background-color: #ef4444;
            border-radius: 50%;
            animation: blink 1s infinite;
        }

        @keyframes blink {
            50% {
                opacity: 0.5;
            }
        }
    </style>
@endpush

@section('content')
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="text-white">Monitoring: {{ $user->name }}</h2>
            <a href="{{ route('admin.monitoring.index') }}" class="btn btn-secondary">Back to List</a>
        </div>

        <div class="monitor-screen">
            <div class="live-indicator">
                <span class="dot"></span> LIVE
            </div>
            <img id="stream-image" src="" alt="Waiting for stream...">
            <div id="loading-text" class="text-white position-absolute">Waiting for agent connection...</div>
        </div>

        <div class="row mt-4">
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stat-label">CPU Usage</div>
                    <div class="stat-value" id="cpu-val">0%</div>
                    <div class="progress mt-2" style="height: 6px; background: rgba(255,255,255,0.1);">
                        <div id="cpu-bar" class="progress-bar bg-primary" style="width: 0%"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stat-label">RAM Usage</div>
                    <div class="stat-value" id="ram-val">0 GB</div>
                    <div class="progress mt-2" style="height: 6px; background: rgba(255,255,255,0.1);">
                        <div id="ram-bar" class="progress-bar bg-success" style="width: 0%"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stat-label">Last Updated</div>
                    <div class="stat-value" id="last-update">-</div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script type="module">
        const userId = {{ $user->id }};
        const imgEl = document.getElementById('stream-image');
        const loadingEl = document.getElementById('loading-text');

        // Listen to private channel
        window.Echo.private(`agent-stream.${userId}`)
            .listen('.agent.data', (e) => {
                console.log('Data received');

                // Update image
                if (e.screenImage) {
                    imgEl.src = 'data:image/jpeg;base64,' + e.screenImage;
                    loadingEl.style.display = 'none';
                }

                // Update stats
                if (e.stats) {
                    document.getElementById('cpu-val').innerText = Math.round(e.stats.cpu) + '%';
                    document.getElementById('cpu-bar').style.width = e.stats.cpu + '%';

                    const ramUsed = (e.stats.ram_used / 1024 / 1024 / 1024).toFixed(2);
                    const ramTotal = (e.stats.ram_total / 1024 / 1024 / 1024).toFixed(2);
                    document.getElementById('ram-val').innerText = `${ramUsed} / ${ramTotal} GB`;

                    const ramPercent = (e.stats.ram_used / e.stats.ram_total) * 100;
                    document.getElementById('ram-bar').style.width = ramPercent + '%';

                    const now = new Date();
                    document.getElementById('last-update').innerText = now.toLocaleTimeString();
                }
            });
    </script>
@endpush