@extends('layouts.dashboard')

@push('styles')
<style>
    .glass-card {
        background: rgba(30, 30, 45, 0.7);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 20px;
        transition: all 0.3s ease;
        overflow: hidden;
    }
    .glass-card:hover {
        transform: translateY(-5px);
        background: rgba(40, 40, 60, 0.8);
        border-color: rgba(0, 123, 255, 0.5);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
    }
    .status-badge {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 8px;
    }
    .status-online { background: #28a745; box-shadow: 0 0 10px #28a745; }
    .status-offline { background: #6c757d; }
    
    .device-icon-container {
        width: 80px;
        height: 80px;
        background: rgba(0, 123, 255, 0.1);
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
    }
    .device-icon-container i {
        font-size: 35px;
        background: linear-gradient(45deg, #007bff, #00d2ff);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    .premium-title {
        font-weight: 800;
        letter-spacing: -0.5px;
        background: linear-gradient(to right, #ffffff, #a0a0a0);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
</style>
@endpush

@section('content')
    <div class="page-header fade-in mb-5">
        <div>
            <h1 class="premium-title display-5">Connected Devices</h1>
            <p class="text-muted">Monitor live activity across all installed agent instances.</p>
        </div>
    </div>

    <div class="row">
        @forelse($devices as $device)
            @php 
                $isOnline = $device->last_seen && $device->last_seen->diffInMinutes(now()) < 5;
                $displayName = $device->user ? $device->user->name : $device->computer_name;
            @endphp
            <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
                <div class="glass-card h-100 fade-in">
                    <div class="card-body text-center p-4">
                        <div class="device-icon-container">
                            <i class="fas fa-microchip"></i>
                        </div>
                        
                        <div class="d-flex align-items-center justify-content-center mb-2">
                            <span class="status-badge {{ $isOnline ? 'status-online' : 'status-offline' }}"></span>
                            <span class="small {{ $isOnline ? 'text-success' : 'text-muted' }} fw-bold">
                                {{ $isOnline ? 'LIVE' : 'OFFLINE' }}
                            </span>
                        </div>

                        <h5 class="text-white mb-1">{{ $displayName }}</h5>
                        <p class="text-muted small mb-4">
                            @if($device->user)
                                <i class="fas fa-user-circle me-1"></i> Logged in as {{ $device->user->email }}
                            @else
                                <i class="fas fa-desktop me-1"></i> {{ $device->computer_name }}
                            @endif
                        </p>

                        <a href="{{ route('admin.monitoring.show', $device->id) }}" class="btn btn-primary btn-round w-100 py-2 shadow-sm">
                            <i class="fas fa-play me-2"></i> {{ $isOnline ? 'Open Monitor' : 'View Logs' }}
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5">
                <div class="glass-card p-5 d-inline-block" style="max-width: 500px;">
                    <i class="fas fa-ghost fa-4x text-muted mb-4"></i>
                    <h3 class="text-white">Waiting for Connections...</h3>
                    <p class="text-muted">No devices have been detected yet. Ensure the Agent App is installed and running on target computers.</p>
                    <a href="{{ route('admin.monitoring.index') }}" class="btn btn-outline-primary mt-4">
                        <i class="fas fa-sync-alt me-2"></i> Refresh Discovery
                    </a>
                </div>
            </div>
        @endforelse
    </div>
@endsection
