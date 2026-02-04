@extends('layouts.app')

@section('content')
    <div class="auth-wrapper">
        <div class="auth-background">
            <div class="blob blob-1"></div>
            <div class="blob blob-2"></div>
        </div>

        <div class="auth-card fade-in">
            <div class="auth-header">
                <h1 class="auth-logo">Account Recovery</h1>
                <p class="auth-subtitle">Enter your email to reset your password.</p>
            </div>

            @if ($errors->any())
                <div class="aero-alert aero-alert-danger mb-3">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('password.verify') }}" method="POST">
                @csrf

                <div class="aero-form-group">
                    <input type="email" name="email" id="email" class="aero-input-floating" placeholder=" " required>
                    <label for="email" class="aero-label-floating">Email Address</label>
                </div>

                <button type="submit" class="aero-btn-premium w-100">
                    <i class="fas fa-check-circle me-2"></i>Verify Email
                </button>

                <div class="auth-footer">
                    <a href="{{ route('login') }}" class="auth-link">Back to Login</a>
                </div>
            </form>
        </div>
    </div>
@endsection