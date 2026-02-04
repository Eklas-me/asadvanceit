@extends('layouts.app')

@section('content')
    <div class="auth-wrapper">
        <!-- Animated Blobs -->
        <div class="auth-background">
            <div class="blob blob-1"></div>
            <div class="blob blob-2"></div>
            <div class="blob blob-3"></div>
        </div>

        <div class="auth-card fade-in">
            <div class="auth-header">
                <h1 class="auth-logo">Advanced IT</h1>
                <p class="auth-subtitle">Welcome back! Please sign in.</p>
            </div>

            @if (session('success'))
                <div class="aero-alert aero-alert-success mb-3">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="aero-alert aero-alert-danger mb-3">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            {{-- Agent-Only Login Notice --}}
            <div class="text-center" style="padding: 2rem 0;">
                <div style="font-size: 4rem; margin-bottom: 1rem;">
                    <i class="fas fa-desktop" style="color: var(--primary-color, #6366f1);"></i>
                </div>
                <h2 style="color: #fff; margin-bottom: 1rem;">Agent App Required</h2>
                <p style="color: rgba(255,255,255,0.7); margin-bottom: 1.5rem; line-height: 1.6;">
                    Direct login is not available.<br>
                    Please use the <strong>Agent App</strong> installed on your PC to sign in.
                </p>
                <div style="background: rgba(255,255,255,0.1); padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
                    <p style="color: rgba(255,255,255,0.6); font-size: 0.85rem; margin: 0;">
                        <i class="fas fa-info-circle me-2"></i>
                        If you don't have the Agent App, please contact your administrator.
                    </p>
                </div>
                <a href="{{ route('register') }}" class="auth-link" style="display: inline-block;">
                    <i class="fas fa-user-plus me-2"></i>Create Account
                </a>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const alert = document.querySelector('.aero-alert');
        if (alert) {
            setTimeout(() => {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            }, 5000);
        }
    </script>
@endpush