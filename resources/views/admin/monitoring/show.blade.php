@extends('layouts.dashboard')

@push('styles')
    <style>
        .monitor-screen {
            width: 100%;
            max-width: 1000px;
            margin: 0 auto;
            background: #000;
            border: 2px solid var(--border-color);
            border-radius: 12px;
            overflow: hidden;
            aspect-ratio: 16/9;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        #stream-image {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .stats-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 1.5rem;
            margin-top: 1rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .stat-label {
            font-size: 0.8rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 0.5rem;
        }

        .stat-value {
            font-size: 1.8rem;
            font-weight: bold;
            color: var(--text-primary);
        }

        .live-indicator {
            position: absolute;
            top: 15px;
            right: 15px;
            background: rgba(0, 0, 0, 0.7);
            color: #ef4444;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 8px;
            backdrop-filter: blur(4px);
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
    <div class="page-header fade-in">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="page-title">Monitoring: {{ $user->name }}</h1>
                <p class="text-muted mb-0">{{ $user->email }}</p>
            </div>
            <a href="{{ route('admin.monitoring.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i> Back to List
            </a>
        </div>
    </div>

    <div class="monitor-screen fade-in">
        <div class="live-indicator">
            <span class="dot"></span> LIVE
        </div>
        <img id="stream-image" src="" alt="Waiting for stream...">
        <div id="loading-text" class="text-muted position-absolute">
            <i class="fas fa-spinner fa-spin me-2"></i> Waiting for agent connection...
        </div>
    </div>

    <div class="row mt-4 fade-in" style="animation-delay: 0.2s;">
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