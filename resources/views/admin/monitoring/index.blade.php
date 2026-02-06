@extends('layouts.dashboard')

@section('content')
    <div class="page-header fade-in">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="page-title">Live Monitoring</h1>
            <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <div class="row">
        @forelse($users as $user)
            <div class="col-md-4 mb-4">
                <div class="aero-card h-100 fade-in">
                    <div class="card-body text-center p-4">
                        <div class="mb-3 position-relative d-inline-block">
                            <i class="fas fa-desktop fa-3x text-primary"></i>
                            <span
                                class="position-absolute top-0 start-100 translate-middle p-2 bg-success border border-light rounded-circle">
                                <span class="visually-hidden">Online</span>
                            </span>
                        </div>
                        <h5 class="card-title mt-2">{{ $user->name }}</h5>
                        <p class="text-muted small mb-4">{{ $user->email }}</p>

                        <a href="{{ route('admin.monitoring.show', $user->id) }}" class="aero-btn w-100">
                            <i class="fas fa-eye me-2"></i> Watch Stream
                        </a>
                    </div>
                    <div class="card-footer bg-transparent border-top-0 text-center pb-3">
                        <small class="text-muted">Last seen:
                            {{ $user->last_seen ? $user->last_seen->diffForHumans() : 'Just now' }}</small>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="aero-card fade-in text-center p-5">
                    <div class="mb-3">
                        <i class="fas fa-satellite-dish fa-3x text-muted"></i>
                    </div>
                    <h3>No Active Agents</h3>
                    <p class="text-muted">No agents have been active in the last 5 minutes.</p>
                    <p class="small text-muted">Ask your team to log in to the Agent App.</p>
                    <a href="{{ route('admin.monitoring.index') }}" class="btn btn-sm btn-outline-primary mt-3">
                        <i class="fas fa-sync-alt me-1"></i> Refresh List
                    </a>
                </div>
            </div>
        @endforelse
    </div>
@endsection