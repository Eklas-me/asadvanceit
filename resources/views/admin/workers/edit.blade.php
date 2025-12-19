@extends('layouts.dashboard')

@section('content')
    <div class="page-header fade-in">
        <h1 class="page-title">Edit Worker</h1>
    </div>

    <div class="row">
        <div class="col-12">
            @if ($errors->any())
                <div class="aero-alert aero-alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="aero-card">
                <div class="aero-card-header">
                    <h2 class="aero-card-title">Update User Form</h2>
                </div>
                <form action="{{ route('admin.workers.update', $worker->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="aero-input" name="name" id="name" required
                                value="{{ old('name', $worker->name) }}">
                        </div>

                        <div class="col-md-6">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="aero-input" name="email" id="email" required
                                value="{{ old('email', $worker->email) }}">
                        </div>

                        <div class="col-md-6">
                            <label for="phone" class="form-label">Phone (Optional)</label>
                            <input type="text" class="aero-input" name="phone" id="phone"
                                value="{{ old('phone', $worker->phone) }}">
                        </div>

                        <div class="col-md-6">
                            <label for="shift" class="form-label">Shift</label>
                            <select class="aero-select" name="shift" id="shift" required>
                                <option value="">-- Select Shift --</option>
                                @foreach(['Morning 8 Hours Female', 'Morning 8 Hours', 'Evening 8 Hours', 'Night 8 Hours', 'Day 12 Hours', 'Night 12 Hours'] as $shift)
                                    <option value="{{ $shift }}" {{ old('shift', $worker->shift) == $shift ? 'selected' : '' }}>
                                        {{ $shift }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-12">
                            <label for="profile_photo" class="form-label">Profile Photo</label>
                            <input type="file" class="aero-input" name="profile_photo" id="profile_photo">
                            @if($worker->profile_photo)
                                <div class="mt-3">
                                    <img src="{{ asset('uploads/' . $worker->profile_photo) }}"
                                        style="width: 100px; height: 100px; border-radius: 8px; object-fit: cover; border: 1px solid var(--border-color);">
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="mt-4 d-flex gap-2">
                        <button type="submit" class="aero-btn aero-btn-primary">
                            <i class="fas fa-check me-1"></i> Update
                        </button>
                        <a href="{{ route('admin.workers.index') }}" class="aero-btn"
                            style="background: #EF4444; color: white; border: none;">
                            <i class="fas fa-times me-1"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection