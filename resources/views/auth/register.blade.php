@extends('layouts.app')

@section('content')
  <div class="auth-wrapper">
    <!-- Animated Blobs -->
    <div class="auth-background">
      <div class="blob blob-1"></div>
      <div class="blob blob-2"></div>
      <div class="blob blob-3"></div>
    </div>

    <div class="auth-card wide fade-in">
      <div class="auth-header">
        <h1 class="auth-logo">Create Account</h1>
        <p class="auth-subtitle">Join us and start your journey</p>
      </div>

      @if ($errors->any())
        <div class="aero-alert aero-alert-danger mb-3">
          @foreach ($errors->all() as $error)
            <div>{{ $error }}</div>
          @endforeach
        </div>
      @endif

      <form action="{{ route('register') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="form-grid">
          <div class="aero-form-group">
            <input type="text" name="name" id="name" class="aero-input-floating" placeholder=" " required
              value="{{ old('name') }}">
            <label for="name" class="aero-label-floating">Full Name</label>
          </div>

          <div class="aero-form-group">
            <input type="email" name="email" id="email" class="aero-input-floating" placeholder=" " required
              value="{{ old('email') }}">
            <label for="email" class="aero-label-floating">Email Address</label>
          </div>
        </div>

        <div class="form-grid">
          <div class="aero-form-group">
            <input type="text" name="phone" id="phone" class="aero-input-floating" placeholder=" "
              value="{{ old('phone') }}">
            <label for="phone" class="aero-label-floating">Phone (Optional)</label>
          </div>

          <div class="aero-form-group">
            <select name="shift" id="shift" class="aero-select-floating" required>
              <option value=""></option> <!-- Empty value for placeholder behavior -->
              <option value="Morning 8 Hours Female">Morning 8 Hours Female</option>
              <option value="Morning 8 Hours">Morning 8 Hours</option>
              <option value="Evening 8 Hours">Evening 8 Hours</option>
              <option value="Night 8 Hours">Night 8 Hours</option>
              <option value="Day 12 Hours">Day 12 Hours</option>
              <option value="Night 12 Hours">Night 12 Hours</option>
            </select>
            <label for="shift" class="aero-label-floating">Select Shift</label>
          </div>
        </div>

        <div class="form-grid">
          <div class="aero-form-group">
            <input type="password" name="password" id="password" class="aero-input-floating" placeholder=" " required>
            <label for="password" class="aero-label-floating">Password</label>
          </div>

          <div class="aero-form-group">
            <!-- File input is tricky to float, using simpler premium style -->
            <input type="file" name="profile_photo" id="profile_photo" class="aero-file-input" accept="image/*">
          </div>
        </div>

        <input type="hidden" name="role" value="user">

        <button type="submit" class="aero-btn-premium w-100 mt-3">
          <i class="fas fa-user-plus me-2"></i>Create Account
        </button>

        <div class="auth-footer" style="justify-content: center;">
          <a href="{{ route('login') }}" class="auth-link">
            <i class="fas fa-arrow-left me-1"></i>Back to Login
          </a>
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