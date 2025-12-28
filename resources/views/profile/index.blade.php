@extends('layouts.dashboard')

@section('content')
    <div class="page-header fade-in">
        <h1 class="page-title">
            <i class="fas fa-user-circle me-2"></i>My Profile
        </h1>
    </div>

    @if (session('success'))
        <div class="aero-alert aero-alert-success mb-3 fade-in">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="aero-alert aero-alert-danger mb-3 fade-in">
            @foreach ($errors->all() as $error)
                <div><i class="fas fa-exclamation-circle me-2"></i>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="row g-4">
        <!-- Profile Overview Card -->
        <div class="col-lg-4">
            <div class="aero-card fade-in" style="animation-delay: 0.1s;">
                <div class="profile-overview">
                    <div class="profile-cover"></div>
                    <div class="profile-avatar-wrapper">
                        @if($user->profile_photo)
                            <img src="{{ asset('storage/' . $user->profile_photo) }}" alt="{{ $user->name }}"
                                class="profile-avatar-large">
                        @else
                            <div class="profile-avatar-large">
                                <span class="avatar-initial-large">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                            </div>
                        @endif
                        <button type="button" class="avatar-upload-btn"
                            onclick="document.getElementById('avatar-input').click()">
                            <i class="fas fa-camera"></i>
                        </button>
                    </div>
                    <div class="profile-info-center">
                        <h3 class="profile-name-large">{{ $user->name }}</h3>
                        <p class="profile-role-large">
                            <i class="fas fa-{{ $user->isAdmin() ? 'shield-alt' : 'user' }} me-1"></i>
                            {{ ucfirst($user->role) }}
                        </p>
                        <div class="profile-stats">
                            <div class="stat-item">
                                <div class="stat-value">{{ $user->liveTokens()->whereDate('created_at', today())->count() }}
                                </div>
                                <div class="stat-label">Tokens Today</div>
                            </div>
                            <div class="stat-divider"></div>
                            <div class="stat-item">
                                <div class="stat-value">
                                    <span
                                        class="aero-badge aero-badge-{{ $user->status === 'active' ? 'success' : ($user->status === 'pending' ? 'warning' : 'danger') }}">
                                        {{ ucfirst($user->status) }}
                                    </span>
                                </div>
                                <div class="stat-label">Status</div>
                            </div>
                        </div>
                    </div>
                    <div class="profile-details-list">
                        <div class="detail-item">
                            <i class="fas fa-envelope detail-icon"></i>
                            <div class="detail-content">
                                <div class="detail-label">Email</div>
                                <div class="detail-value">{{ $user->email }}</div>
                            </div>
                        </div>
                        @if($user->phone)
                            <div class="detail-item">
                                <i class="fas fa-phone detail-icon"></i>
                                <div class="detail-content">
                                    <div class="detail-label">Phone</div>
                                    <div class="detail-value">{{ $user->phone }}</div>
                                </div>
                            </div>
                        @endif
                        @if($user->shift)
                            <div class="detail-item">
                                <i class="fas fa-clock detail-icon"></i>
                                <div class="detail-content">
                                    <div class="detail-label">Shift</div>
                                    <div class="detail-value">{{ $user->shift }}</div>
                                </div>
                            </div>
                        @endif
                        <div class="detail-item">
                            <i class="fas fa-calendar detail-icon"></i>
                            <div class="detail-content">
                                <div class="detail-label">Member Since</div>
                                <div class="detail-value">{{ $user->created_at->format('M d, Y') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Profile & Password -->
        <div class="col-lg-8">
            <!-- Edit Profile Form -->
            <div class="aero-card fade-in mb-4" style="animation-delay: 0.2s;">
                <div class="aero-card-header">
                    <h2 class="aero-card-title">
                        <i class="fas fa-edit me-2"></i>Edit Profile
                    </h2>
                </div>
                <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="file" id="avatar-input" name="profile_photo" accept="image/*" style="display: none;"
                        onchange="this.form.submit()">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="aero-form-group">
                                <label class="aero-label">
                                    <i class="fas fa-user me-1"></i>Full Name
                                </label>
                                <input type="text" name="name" class="aero-input" value="{{ $user->name }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="aero-form-group">
                                <label class="aero-label">
                                    <i class="fas fa-envelope me-1"></i>Email Address
                                </label>
                                <input type="email" name="email" class="aero-input" value="{{ $user->email }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="aero-form-group">
                                <label class="aero-label">
                                    <i class="fas fa-phone me-1"></i>Phone Number
                                </label>
                                <input type="text" name="phone" class="aero-input" value="{{ $user->phone }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="aero-form-group">
                                <label class="aero-label">
                                    <i class="fas fa-clock me-1"></i>Shift
                                </label>
                                <select name="shift" class="aero-input">
                                    <option value="">-- Select Shift --</option>
                                    <option value="Morning 8 Hours Female" {{ $user->shift === 'Morning 8 Hours Female' ? 'selected' : '' }}>Morning 8 Hours Female</option>
                                    <option value="Morning 8 Hours" {{ $user->shift === 'Morning 8 Hours' ? 'selected' : '' }}>Morning 8 Hours</option>
                                    <option value="Evening 8 Hours" {{ $user->shift === 'Evening 8 Hours' ? 'selected' : '' }}>Evening 8 Hours</option>
                                    <option value="Night 8 Hours" {{ $user->shift === 'Night 8 Hours' ? 'selected' : '' }}>
                                        Night 8 Hours</option>
                                    <option value="Day 12 Hours" {{ $user->shift === 'Day 12 Hours' ? 'selected' : '' }}>Day
                                        12 Hours</option>
                                    <option value="Night 12 Hours" {{ $user->shift === 'Night 12 Hours' ? 'selected' : '' }}>
                                        Night 12 Hours</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4">
                        <button type="submit" class="aero-btn aero-btn-primary">
                            <i class="fas fa-save me-2"></i>Save Changes
                        </button>
                    </div>
                </form>
            </div>

            <!-- Change Password Form -->
            <div class="aero-card fade-in" style="animation-delay: 0.3s;">
                <div class="aero-card-header">
                    <h2 class="aero-card-title">
                        <i class="fas fa-lock me-2"></i>Change Password
                    </h2>
                </div>
                <form action="{{ route('profile.password') }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-12">
                            <div class="aero-form-group">
                                <label class="aero-label">
                                    <i class="fas fa-key me-1"></i>Current Password
                                </label>
                                <input type="password" name="current_password" class="aero-input" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="aero-form-group">
                                <label class="aero-label">
                                    <i class="fas fa-lock me-1"></i>New Password
                                </label>
                                <input type="password" name="new_password" class="aero-input" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="aero-form-group">
                                <label class="aero-label">
                                    <i class="fas fa-check-circle me-1"></i>Confirm New Password
                                </label>
                                <input type="password" name="new_password_confirmation" class="aero-input" required>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4">
                        <button type="submit" class="aero-btn aero-btn-primary">
                            <i class="fas fa-shield-alt me-2"></i>Update Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        /* Profile Overview Styles */
        .profile-overview {
            position: relative;
        }

        .profile-cover {
            height: 120px;
            background: var(--accent-gradient);
            border-radius: 12px 12px 0 0;
            margin: -24px -24px 0;
        }

        .profile-avatar-wrapper {
            position: relative;
            display: flex;
            justify-content: center;
            margin-top: -60px;
            margin-bottom: 16px;
        }

        .profile-avatar-large {
            width: 120px;
            height: 120px;
            border-radius: 20px;
            border: 4px solid var(--card-bg);
            overflow: hidden;
            background: var(--accent-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
        }

        .profile-avatar-large img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .avatar-initial-large {
            font-size: 48px;
            font-weight: 700;
            color: white;
        }

        .avatar-upload-btn {
            position: absolute;
            bottom: 0;
            right: calc(50% - 70px);
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: var(--accent-blue);
            color: white;
            border: 3px solid var(--card-bg);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
        }

        .avatar-upload-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 16px rgba(59, 130, 246, 0.5);
        }

        .profile-info-center {
            text-align: center;
            padding: 0 24px 24px;
            border-bottom: 1px solid var(--border-light);
        }

        .profile-name-large {
            font-size: 24px;
            font-weight: 700;
            color: var(--text-primary);
            margin: 0 0 4px;
        }

        .profile-role-large {
            font-size: 14px;
            color: var(--text-secondary);
            margin: 0 0 20px;
        }

        .profile-stats {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 24px;
        }

        .stat-item {
            text-align: center;
        }

        .stat-value {
            font-size: 24px;
            font-weight: 700;
            color: var(--accent-blue);
            line-height: 1;
            margin-bottom: 4px;
        }

        .stat-label {
            font-size: 12px;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-divider {
            width: 1px;
            height: 40px;
            background: var(--border-light);
        }

        .profile-details-list {
            padding: 24px 0 0;
        }

        .detail-item {
            display: flex;
            align-items: flex-start;
            gap: 16px;
            padding: 16px 24px;
            border-radius: 8px;
            transition: background 0.2s ease;
        }

        .detail-item:hover {
            background: var(--hover-bg);
        }

        .detail-icon {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--bg-tertiary);
            border-radius: 10px;
            color: var(--accent-blue);
            font-size: 16px;
            flex-shrink: 0;
        }

        .detail-content {
            flex: 1;
        }

        .detail-label {
            font-size: 12px;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .detail-value {
            font-size: 14px;
            color: var(--text-primary);
            font-weight: 500;
        }

        .aero-form-group {
            margin-bottom: 0;
        }

        .aero-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            font-size: 13px;
            color: var(--text-primary);
        }
    </style>
@endsection