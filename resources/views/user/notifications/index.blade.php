@extends('layouts.dashboard')

@section('content')
    <div class="page-header fade-in">
        <h1 class="page-title">Notifications</h1>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="aero-card">
                <div class="aero-card-header">
                    <h2 class="aero-card-title">
                        <i class="fas fa-bell me-2"></i>All Notifications
                    </h2>
                </div>
                @if($notifications->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($notifications as $notification)
                            <div class="list-group-item"
                                style="background: transparent; border: none; border-bottom: 1px solid var(--border-light); padding: 20px 0; opacity: {{ $notification->status === 'read' ? '0.6' : '1' }}">
                                <div class="d-flex w-100 justify-content-between align-items-start mb-2">
                                    <h5 style="color: var(--text-primary); font-weight: 600; margin: 0;">
                                        {{ $notification->title }}
                                    </h5>
                                    <div>
                                        <small style="color: var(--text-muted); margin-right: 10px;">
                                            {{ $notification->created_at->diffForHumans() }}
                                        </small>
                                        @if($notification->status === 'unread')
                                            <form action="{{ route('notifications.read', $notification->id) }}" method="POST"
                                                style="display: inline;">
                                                @csrf
                                                <button type="submit" class="aero-badge aero-badge-primary"
                                                    style="border: none; cursor: pointer;">
                                                    <i class="fas fa-check me-1"></i> Mark as Read
                                                </button>
                                            </form>
                                        @else
                                            <span class="aero-badge aero-badge-success">
                                                <i class="fas fa-check-double me-1"></i> Read
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <p style="color: var(--text-secondary); margin-bottom: 8px;">
                                    {{ $notification->message }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center" style="padding: 60px 20px;">
                        <i class="fas fa-bell-slash fa-4x mb-3" style="color: var(--text-muted); opacity: 0.2;"></i>
                        <p style="color: var(--text-muted);">No notifications yet.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection