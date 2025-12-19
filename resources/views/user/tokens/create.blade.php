@extends('layouts.dashboard')

@section('content')
    <div class="page-header fade-in">
        <h1 class="page-title">Add Tokens</h1>
    </div>

    <div class="row">
        <div class="col-12">
            @if(session('success'))
                <div class="aero-alert aero-alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="aero-alert aero-alert-danger">{{ session('error') }}</div>
            @endif

            <div class="aero-card">
                <div class="aero-card-header">
                    <h2 class="aero-card-title">
                        <i class="fas fa-plus-circle me-2"></i>Submit New Tokens
                    </h2>
                </div>
                <form action="{{ route('tokens.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="tinder_token" class="form-label">
                            Enter Tokens (One per line)
                        </label>
                        <textarea class="aero-textarea" id="tinder_token" name="tinder_token" rows="10" required
                            placeholder="Enter tokens here, one per line..."></textarea>
                        @error('tinder_token')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                        <small style="color: var(--text-muted); display: block; margin-top: 8px;">
                            <i class="fas fa-info-circle me-1"></i>You can paste multiple tokens, each on a new line
                        </small>
                    </div>

                    <button type="submit" class="aero-btn aero-btn-primary">
                        <i class="fas fa-paper-plane me-2"></i>Submit Tokens
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection