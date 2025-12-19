@extends('layouts.dashboard')

@section('content')
    <div class="page-header fade-in">
        <h1 class="page-title">Settings</h1>
    </div>

    @if(session('success'))
        <div class="aero-alert aero-alert-success mb-4">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="aero-alert aero-alert-danger mb-4">
            <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="aero-alert aero-alert-danger mb-4">
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif


    <div class="row">
        <div class="col-md-6">
            <div class="aero-card mb-4">
                <div class="aero-card-header">
                    <h3 class="aero-card-title">Site Branding</h3>
                </div>
                <div class="card-body">

                    <!-- Logo Settings -->
                    <div class="mb-4 pb-4 border-bottom">
                        <h5 class="mb-3">Site Logo</h5>
                        <div class="d-flex align-items-center mb-3">
                            <div class="p-3 border rounded bg-light me-3">
                                <img src="{{ asset(getSetting('site_logo', 'images/logo.png')) }}" alt="Current Logo"
                                    style="max-height: 40px; max-width: 150px;">
                            </div>
                            <div>
                                <small class="text-muted d-block">Recommended: 200x60px</small>
                                <small class="text-muted d-block">Max: 500x150px (2MB)</small>
                            </div>
                        </div>
                        <form action="{{ route('admin.settings.update_logo') }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            <div class="input-group">
                                <input type="file" name="logo" class="form-control" accept="image/*">
                                <button type="submit" class="aero-btn aero-btn-primary">Upload</button>
                                @if(getSetting('site_logo'))
                                    <button type="button" class="btn btn-danger"
                                        onclick="if(confirm('Are you sure?')) document.getElementById('remove-logo-form').submit();">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                @endif
                            </div>
                        </form>
                        @if(getSetting('site_logo'))
                            <form id="remove-logo-form" action="{{ route('admin.settings.remove_logo') }}" method="POST"
                                style="display: none;">
                                @csrf
                                @method('DELETE')
                            </form>
                        @endif
                    </div>

                    <!-- Favicon Settings -->
                    <div>
                        <h5 class="mb-3">Favicon</h5>
                        <div class="d-flex align-items-center mb-3">
                            <div class="p-2 border rounded bg-light me-3">
                                <img src="{{ asset(getSetting('site_favicon', 'favicon.ico')) }}" alt="Favicon"
                                    style="width: 32px; height: 32px;">
                            </div>
                            <div>
                                <small class="text-muted d-block">Recommended: 32x32px</small>
                                <small class="text-muted d-block">Max: 64x64px (ICO/PNG)</small>
                            </div>
                        </div>
                        <form action="{{ route('admin.settings.update_favicon') }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            <div class="input-group">
                                <input type="file" name="favicon" class="form-control" accept=".ico,.png">
                                <button type="submit" class="aero-btn aero-btn-primary">Upload</button>
                                @if(getSetting('site_favicon'))
                                    <button type="button" class="btn btn-danger"
                                        onclick="if(confirm('Are you sure?')) document.getElementById('remove-favicon-form').submit();">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                @endif
                            </div>
                        </form>
                        @if(getSetting('site_favicon'))
                            <form id="remove-favicon-form" action="{{ route('admin.settings.remove_favicon') }}" method="POST"
                                style="display: none;">
                                @csrf
                                @method('DELETE')
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <!-- Telegram Settings -->
            <div class="aero-card mb-4">
                <div class="aero-card-header">
                    <h3 class="aero-card-title">Telegram Notifications</h3>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">
                        Configure Telegram to receive notifications on user login.
                    </p>

                    <form action="{{ route('admin.settings.update_telegram') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Bot Token</label>
                            <input type="text" name="telegram_bot_token" class="form-control"
                                value="{{ getSetting('telegram_bot_token') }}" placeholder="8388733588:AAHwf..." required>
                            <small class="text-muted">Get this from <a href="https://t.me/botfather"
                                    target="_blank">@BotFather</a></small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Admin Chat ID / Group ID</label>
                            <input type="text" name="telegram_admin_chat_id" class="form-control"
                                value="{{ getSetting('telegram_admin_chat_id') }}" placeholder="1314727804 or -100xxxxxxxx"
                                required>
                            <small class="text-muted">
                                Enter your Chat ID or Group ID. For multiple recipients, separate with commas (e.g.
                                <code>12345, -67890</code>).
                                <br>
                                Send a message to your bot in the group/chat, then visit <a
                                    href="{{ url('admin/telegram/test') }}" target="_blank">Test Connection</a> to check.
                            </small>
                        </div>

                        <button type="submit" class="aero-btn aero-btn-primary w-100">
                            <i class="fas fa-save me-2"></i> Save Telegram Settings
                        </button>
                    </form>

                    <div class="mt-3 pt-3 border-top text-center">
                        <a href="{{ url('admin/telegram/test') }}" target="_blank" class="text-decoration-none">
                            <i class="fas fa-plug me-1"></i> Test Connection
                        </a>
                    </div>
                </div>
            </div>

            <!-- Sheet Management -->
            <div class="aero-card mb-4">
                <div class="aero-card-header">
                    <h3 class="aero-card-title">Sheet Management</h3>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-4">
                        Toggle the visibility of Google Sheets in the sidebar.
                    </p>

                    <form action="{{ route('admin.settings.update_sheets') }}" method="POST">
                        @csrf
                        @php
                            $sheets = [
                                'facebook' => 'Facebook',
                                'morning_8_hours' => 'Morning 8 Hours',
                                'morning_8_hours_female' => 'Morning 8 Hours Female',
                                'evening_8_hours' => 'Evening 8 Hours',
                                'night_8_hours' => 'Night 8 Hours',
                                'day_12_hours' => 'Day 12 Hours',
                                'night_12_hours' => 'Night 12 Hours',
                            ];
                            $visibility = json_decode(getSetting('sheet_visibility', '{}'), true);
                        @endphp

                        @foreach($sheets as $key => $title)
                            <div class="form-check form-switch mb-3 d-flex align-items-center justify-content-between p-0">
                                <label class="form-check-label ms-0" for="sheet_{{ $key }}">
                                    {{ $title }}
                                </label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="sheets[{{ $key }}]"
                                        id="sheet_{{ $key }}" {{ ($visibility[$key] ?? 'on') === 'on' ? 'checked' : '' }}
                                        style="width: 40px; height: 20px; cursor: pointer;">
                                </div>
                            </div>
                        @endforeach

                        <button type="submit" class="aero-btn aero-btn-primary w-100 mt-3">
                            <i class="fas fa-save me-2"></i> Save Visibility Settings
                        </button>
                    </form>
                </div>
            </div>

            <div class="aero-card">
                <div class="aero-card-header">
                    <h3 class="aero-card-title">Cache Management</h3>
                </div>
                <div class="card-body">
                    <p class="var(--text-muted) mb-4">
                        Clear the system cache to refresh data such as the "Workers List" and "Token Counts".
                        Use this if you have manually updated the database or if you suspect data is stale.
                    </p>

                    <form action="{{ route('admin.settings.clear_cache') }}" method="POST">
                        @csrf
                        <button type="submit" class="aero-btn aero-btn-danger w-100">
                            <i class="fas fa-trash-alt me-2"></i> Clear System Cache
                        </button>
                        <small class="d-block text-center mt-2 text-muted">
                            Excecutes: <code>php artisan optimize:clear</code>
                        </small>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection