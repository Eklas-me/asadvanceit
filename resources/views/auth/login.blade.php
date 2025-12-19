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

            <form action="{{ route('login') }}" method="POST">
                @csrf

                <div class="aero-form-group">
                    <input type="email" name="email" id="email" class="aero-input-floating" placeholder=" " required
                        value="{{ old('email') }}">
                    <label for="email" class="aero-label-floating">Email Address</label>
                </div>

                <div class="aero-form-group">
                    <input type="password" name="password" id="password" class="aero-input-floating" placeholder=" "
                        required>
                    <label for="password" class="aero-label-floating">Password</label>
                </div>

                <button type="submit" class="aero-btn-premium w-100">
                    <i class="fas fa-sign-in-alt me-2"></i>Sign In
                </button>

                <div class="auth-footer">
                    <a href="#" class="auth-link">Forgot Password?</a>
                    <a href="{{ route('register') }}" class="auth-link">Create Account</a>
                </div>
            </form>
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