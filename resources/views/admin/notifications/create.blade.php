@extends('layouts.dashboard')

@section('content')
    <div class="page-header fade-in">
        <h1 class="page-title">Add Notification</h1>
    </div>

    <div class="row">
        <div class="col-12">
            @if(session('success'))
                <div class="aero-alert aero-alert-success">{{ session('success') }}</div>
            @endif

            <div class="aero-card">
                <div class="aero-card-header">
                    <h2 class="aero-card-title">
                        <i class="fas fa-plus-circle me-2"></i>Create New Notification
                    </h2>
                </div>
                <form action="{{ route('admin.notifications.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="user_id" class="form-label">Target User</label>
                        <select class="aero-input" name="user_id" id="user_id" required>
                            <option value="all">All Users</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="title" class="form-label">Title</label>
                        <input type="text" class="aero-input" name="notification_title" id="title" required
                            placeholder="Enter notification title">
                        @error('notification_title')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="message" class="form-label">Message</label>
                        <textarea class="aero-textarea" id="message" name="message" rows="4" required
                            placeholder="Enter notification message"></textarea>
                        @error('message')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <button type="submit" class="aero-btn aero-btn-primary">
                        <i class="fas fa-paper-plane me-2"></i>Submit
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection