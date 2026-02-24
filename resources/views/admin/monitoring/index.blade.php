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
        <!-- Active Device Count Badge -->
        <span class="badge bg-primary text-white rounded-pill px-3 py-2 ms-3 fs-6 shadow-sm" style="background: linear-gradient(45deg, #007bff, #00d2ff) !important; border: 1px solid rgba(255,255,255,0.2);">
            <i class="fas fa-desktop me-2"></i> {{ $devices->count() }} Active {{ Str::plural('Device', $devices->count()) }}
        </span>
    </div>

    <div class="row g-4">
        @forelse($devices as $device)
            @php 
                $isOnline = $device->last_seen && $device->last_seen->diffInMinutes(now()) < 5;
                $displayName = $device->user ? $device->user->name : $device->computer_name;
            @endphp
            <div class="col-xl-3 col-lg-4 col-md-6">
                <!-- Outer Wrapper for animation -->
                <div class="h-100" style="perspective: 1000px;">
                    <div class="glass-card h-100 fade-in position-relative">
                        <!-- Top Accent Line -->
                        <div class="position-absolute top-0 start-0 w-100" style="height: 4px; background: linear-gradient(90deg, #007bff, #00d2ff); opacity: {{ $isOnline ? 1 : 0.3 }}; transition: opacity 0.3s ease;"></div>
                        
                        <div class="card-body text-center p-4 d-flex flex-column h-100">
                            <!-- Status Indicator -->
                            <div class="d-flex justify-content-end mb-2">
                                <span class="badge rounded-pill {{ $isOnline ? 'bg-success bg-opacity-10 text-success border border-success border-opacity-25' : 'bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25' }} px-2 py-1" style="font-size: 0.70rem;">
                                    <span class="status-badge {{ $isOnline ? 'status-online' : 'status-offline' }} me-1 align-middle" style="width: 8px; height: 8px;"></span>
                                    {{ $isOnline ? 'LIVE NOW' : 'OFFLINE' }}
                                </span>
                            </div>

                            <div class="device-icon-container shadow-sm mx-auto mb-3" style="transition: transform 0.3s ease;">
                                <i class="fas fa-microchip"></i>
                            </div>

                            <h5 class="text-white mb-1 fw-bold text-truncate" title="{{ $displayName }}">{{ $displayName }}</h5>
                            
                            <div class="text-muted small mb-4 flex-grow-1">
                                @if($device->user)
                                    <div class="d-flex align-items-center justify-content-center text-truncate" title="{{ $device->user->email }}">
                                        <i class="fas fa-user-circle me-2 text-primary opacity-75"></i> 
                                        <span>{{ $device->user->email }}</span>
                                    </div>
                                    @if($device->computer_name != $displayName)
                                      <div class="mt-1 opacity-50" style="font-size: 0.75rem;"><i class="fas fa-desktop me-1"></i> {{ $device->computer_name }}</div>
                                    @endif
                                @else
                                    <div class="d-flex align-items-center justify-content-center">
                                        <i class="fas fa-desktop me-2 text-primary opacity-75"></i> 
                                        <span>{{ $device->computer_name }}</span>
                                    </div>
                                @endif
                                <div class="mt-2 d-flex justify-content-center align-items-center gap-2">
                                    <div class="opacity-50" style="font-size: 0.7rem;">
                                        <i class="fas fa-clock me-1"></i> Last seen: {{ $device->last_seen ? $device->last_seen->diffForHumans() : 'Never' }}
                                    </div>
                                    @if($device->agent_version)
                                    <span class="badge bg-secondary bg-opacity-25 text-light border border-secondary border-opacity-50 px-2 py-1" style="font-size: 0.65rem;" title="Agent App Version">
                                        v{{ $device->agent_version }}
                                    </span>
                                    @endif
                                </div>
                            </div>

                            <div class="mt-auto pt-3 border-top border-secondary border-opacity-25">
                                <a href="{{ route('admin.monitoring.show', $device->id) }}" class="btn btn-primary btn-round w-100 py-2 shadow-sm d-flex align-items-center justify-content-center" style="transition: all 0.2s ease;">
                                    <i class="fas fa-play me-2"></i> {{ $isOnline ? 'Open Monitor' : 'View Logs' }}
                                </a>
                            </div>
                        </div>
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
