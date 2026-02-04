@extends('layouts.app')

@section('content')
    <div class="auth-wrapper">
        <div class="auth-background">
            <div class="blob blob-3"></div>
        </div>

        <div class="auth-card fade-in">
            <div class="auth-header">
                <h1 class="auth-logo">Reset Password</h1>
                <p class="auth-subtitle">Set a new password for <strong>{{ $email }}</strong></p>
            </div>

            @if ($errors->any())
                <div class="aero-alert aero-alert-danger mb-3">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('password.update.direct') }}" method="POST">
                @csrf

                <div class="aero-form-group">
                    <input type="password" name="password" id="password" class="aero-input-floating" placeholder=" "
                        required>
                    <label for="password" class="aero-label-floating">New Password</label>
                </div>

                <div class="aero-form-group">
                    <input type="password" name="password_confirmation" id="password_confirmation"
                        class="aero-input-floating" placeholder=" " required>
                    <label for="password_confirmation" class="aero-label-floating">Confirm Password</label>
                </div>

                <button type="submit" class="aero-btn-premium w-100">
                    <i class="fas fa-key me-2"></i>Reset Password
                </button>
            </form>
        </div>
    </div>
@endsection