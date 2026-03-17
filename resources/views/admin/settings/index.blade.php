@extends('layouts.dashboard')

@section('content')
    <style>
        .user-selection-list {
            max-height: 250px;
            overflow-y: auto;
            background: rgba(0, 0, 0, 0.2);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 8px;
        }

        .user-selection-item {
            padding: 10px 14px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.2s ease;
            border-radius: 8px;
            margin-bottom: 4px;
        }

        .user-selection-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }

        .user-selection-item:hover {
            background: rgba(255, 255, 255, 0.08);
            transform: translateX(4px);
        }

        .user-selection-item input[type="checkbox"] {
            width: 20px;
            height: 20px;
            accent-color: var(--accent-blue);
            cursor: pointer;
        }

        .user-selection-info {
            display: flex;
            flex-direction: column;
            flex: 1;
        }

        .user-selection-name {
            font-size: 14px;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 2px;
        }

        .user-selection-email {
            font-size: 12px;
            color: var(--text-secondary);
            font-style: italic;
        }

        .user-search-wrapper {
            position: relative;
            margin-bottom: 12px;
        }

        .user-search-wrapper i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-secondary);
            font-size: 14px;
        }

        .user-search-input {
            padding-left: 40px !important;
        }

        /* Custom scrollbar for user list */
        .user-selection-list::-webkit-scrollbar {
            width: 6px;
        }

        .user-selection-list::-webkit-scrollbar-track {
            background: transparent;
        }

        .user-selection-list::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
        }

        .user-selection-list::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.2);
        }
    </style>
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
                            <div class="p-3 border rounded me-3" style="background: var(--bg-tertiary);">
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
                            <div class="p-2 border rounded me-3" style="background: var(--bg-tertiary);">
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
            <!-- Agent App Settings -->
            <div class="aero-card mb-4" id="agent-app-settings">
                <div class="aero-card-header">
                    <h3 class="aero-card-title">Agent App Updates</h3>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">
                        Manage the auto-update metadata for the Agent App.
                    </p>

                    <form action="{{ route('admin.settings.update_agent_app') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Latest Version</label>
                            <input type="text" name="agent_version" class="form-control"
                                value="{{ \App\Models\SiteSetting::get('agent_version', '1.0.0') }}" placeholder="e.g. 1.0.1" required>
                            <small class="text-muted">Current version on clients: 1.0.0</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Update File (Direct Upload)</label>
                            <input type="file" name="agent_update_file" class="form-control" accept=".zip,.msi,.exe">
                            <small class="text-info d-block mt-1">
                                <i class="fas fa-info-circle me-1"></i> Upload the <code>.msi</code> or <code>.exe</code> file generated during build. 
                                <br>Tauri v2 generates these as updater artifacts directly.
                            </small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">OR Manual Download URL</label>
                            <input type="url" name="agent_download_url" class="form-control"
                                value="{{ \App\Models\SiteSetting::get('agent_download_url') }}" 
                                placeholder="https://asadvanceit.com/downloads/agent-1.0.1.msi.zip">
                            <small class="text-muted">Only use this if you have uploaded the file manually to hosting.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Update Signature</label>
                            <textarea name="agent_signature" class="form-control" rows="3" 
                                placeholder="Enter the signature string generated during build..." required>{{ \App\Models\SiteSetting::get('agent_signature') }}</textarea>
                            <small class="text-muted">Generated by <code>npm run tauri build</code></small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Release Notes</label>
                            <textarea name="agent_notes" class="form-control" rows="2" 
                                placeholder="What's new in this version?">{{ \App\Models\SiteSetting::get('agent_notes') }}</textarea>
                        </div>

                        <button type="submit" class="aero-btn aero-btn-primary w-100">
                            <i class="fas fa-save me-2"></i> Save Agent App Settings
                        </button>
                    </form>
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

            <!-- Google Sheets Management -->
            <div class="aero-card mb-4">
                <div class="aero-card-header d-flex justify-content-between align-items-center">
                    <h3 class="aero-card-title mb-0">Manage Google Sheets</h3>
                    <button type="button" class="aero-btn aero-btn-primary btn-sm" onclick="toggleAddSheetForm(this)">
                        <i class="fas fa-plus me-1"></i> Add Sheet
                    </button>
                </div>
                <div class="card-body">
                    <!-- Add New Sheet Form (Hidden by default) -->
                    <div style="display:none;" id="addSheetForm" class="mb-4">
                        <div class="aero-card border-0" style="background: var(--bg-tertiary); box-shadow: inset 0 0 20px rgba(0,0,0,0.1);">
                            <div class="card-body p-4">
                                <h5 class="mb-4" style="color: var(--text-primary); font-weight: 600;">
                                    <i class="fas fa-plus-circle me-2 text-success"></i>Add New Sheet
                                </h5>
                                <form action="{{ route('admin.settings.sheets.store') }}" method="POST">
                                    @csrf
                                    <div class="row">
                                        <div class="col-md-6 mb-3 aero-form-group">
                                            <label class="aero-label">Title <span class="text-danger">*</span></label>
                                            <input type="text" name="title" class="aero-input"
                                                placeholder="e.g. Morning Shift" required>
                                        </div>
                                        <div class="col-md-6 mb-3 aero-form-group">
                                            <label class="aero-label">Icon Class</label>
                                            <input type="text" name="icon" class="aero-input" placeholder="fas fa-file-excel"
                                                value="fas fa-file-excel">
                                            <small class="text-muted d-block mt-1">FontAwesome icon class</small>
                                        </div>
                                    </div>
                                    <div class="mb-3 aero-form-group">
                                        <label class="aero-label">Google Sheet URL <span class="text-danger">*</span></label>
                                        <input type="url" name="url" class="aero-input"
                                            placeholder="https://docs.google.com/spreadsheets/d/..." required>
                                    </div>
                                    <div class="row align-items-end">
                                        <div class="col-md-6 mb-3 aero-form-group">
                                            <label class="aero-label">Permission Type <span class="text-danger">*</span></label>
                                            <select name="permission_type" class="aero-select" id="new_permission_type"
                                                onchange="toggleShiftField(this, 'new_shift_field')" required>
                                                <option value="public">Public (Everyone)</option>
                                                <option value="shift_based">Shift Based</option>
                                                <option value="admin_only">Admin Only</option>
                                                <option value="specific_users">Specific Users</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3 aero-form-group" id="new_shift_field" style="display: none;">
                                            <label class="aero-label">Shift</label>
                                            <select name="shift" class="aero-select">
                                                <option value="">Select Shift...</option>
                                                @foreach(\App\Models\GoogleSheet::getAvailableShifts() as $key => $label)
                                                    <option value="{{ $key }}">{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3 aero-form-group" id="new_users_field" style="display: none;">
                                            <label class="aero-label">Select Users (Active Only)</label>
                                            <div class="user-search-wrapper">
                                                <i class="fas fa-search"></i>
                                                <input type="text" class="aero-input user-search-input" placeholder="Search by name or email..." 
                                                       onkeyup="filterUsers(this, 'new_user_list')">
                                            </div>
                                            <div class="user-selection-list" id="new_user_list">
                                                @foreach($users as $user)
                                                    <label class="user-selection-item">
                                                        <input type="checkbox" name="user_ids[]" value="{{ $user->id }}">
                                                        <div class="user-selection-info">
                                                            <span class="user-selection-name">{{ $user->name }}</span>
                                                            <span class="user-selection-email">{{ $user->email }}</span>
                                                        </div>
                                                    </label>
                                                @endforeach
                                            </div>
                                            <small class="text-muted d-block mt-2"><i class="fas fa-info-circle me-1"></i>Only active users are listed here.</small>
                                        </div>
                                    </div>
                                    <div class="mt-2 text-end">
                                        <button type="submit" class="aero-btn aero-btn-success">
                                            <i class="fas fa-save me-2"></i>Save Sheet
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Existing Sheets List -->
                    @php
                        $googleSheets = \App\Models\GoogleSheet::getAllSheets();
                    @endphp

                    @if($googleSheets->isEmpty())
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-file-excel fa-3x mb-3"></i>
                            <p>No sheets configured yet. Click "Add Sheet" to create one.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Permission</th>
                                        <th class="text-center">Visible</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($googleSheets as $sheet)
                                        <tr>
                                            <td>
                                                <i class="{{ $sheet->icon }} me-2 text-primary"></i>
                                                {{ $sheet->title }}
                                            </td>
                                            <td>
                                                @if($sheet->permission_type === 'public')
                                                    <span class="badge bg-success">Public</span>
                                                @elseif($sheet->permission_type === 'shift_based')
                                                    <span class="badge bg-info">{{ $sheet->shift }}</span>
                                                @elseif($sheet->permission_type === 'specific_users')
                                                    <span class="badge bg-primary">Specific Users</span>
                                                @else
                                                    <span class="badge bg-danger">Admin Only</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <form action="{{ route('admin.settings.sheets.toggle', $sheet->id) }}" method="POST"
                                                    class="d-inline">
                                                    @csrf
                                                    <button type="submit"
                                                        class="btn btn-sm {{ $sheet->is_visible ? 'btn-success' : 'btn-secondary' }}"
                                                        title="{{ $sheet->is_visible ? 'Click to hide' : 'Click to show' }}">
                                                        <i class="fas {{ $sheet->is_visible ? 'fa-eye' : 'fa-eye-slash' }}"></i>
                                                    </button>
                                                </form>
                                            </td>
                                            <td class="text-end">
                                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                                    data-bs-target="#editSheet{{ $sheet->id }}">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <form action="{{ route('admin.settings.sheets.delete', $sheet->id) }}" method="POST"
                                                    class="d-inline"
                                                    onsubmit="return confirm('Are you sure you want to delete this sheet?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                    @endif
                </div>
            </div>

            <script>
                function toggleAddSheetForm(btn) {
                    var form = document.getElementById('addSheetForm');
                    if (form.style.display === 'none' || form.style.display === '') {
                        form.style.display = 'block';
                        btn.innerHTML = '<i class="fas fa-times me-1"></i> Cancel';
                    } else {
                        form.style.display = 'none';
                        btn.innerHTML = '<i class="fas fa-plus me-1"></i> Add Sheet';
                    }
                }

                function toggleShiftField(selectEl, fieldId) {
                    const shiftField = document.getElementById(fieldId);
                    
                    // Determine if this is an edit modal or the new form by checking if it contains an ID
                    const isEdit = fieldId.includes('edit_shift_field_');
                    let usersFieldId = 'new_users_field';
                    
                    if (isEdit) {
                        const id = fieldId.split('_').pop();
                        usersFieldId = 'edit_users_field_' + id;
                    }
                    
                    const usersField = document.getElementById(usersFieldId);

                    if (selectEl.value === 'shift_based') {
                        if (shiftField) shiftField.style.display = 'block';
                        if (usersField) usersField.style.display = 'none';
                    } else if (selectEl.value === 'specific_users') {
                        if (shiftField) shiftField.style.display = 'none';
                        if (usersField) usersField.style.display = 'block';
                    } else {
                        if (shiftField) shiftField.style.display = 'none';
                        if (usersField) usersField.style.display = 'none';
                    }
                }

                function filterUsers(input, listId) {
                    const filter = input.value.toLowerCase();
                    const list = document.getElementById(listId);
                    const items = list.getElementsByClassName('user-selection-item');

                    for (let i = 0; i < items.length; i++) {
                        const name = items[i].querySelector('.user-selection-name').textContent.toLowerCase();
                        const email = items[i].querySelector('.user-selection-email').textContent.toLowerCase();
                        if (name.includes(filter) || email.includes(filter)) {
                            items[i].style.display = "";
                        } else {
                            items[i].style.display = "none";
                        }
                    }
                }
            </script>

            <div class="aero-card">
                <div class="aero-card-header">
                    <h3 class="aero-card-title">Cache Management</h3>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-4">
                        Clear the system cache to refresh data such as the "Workers List" and "Token Counts".
                        Use this if you have manually updated the database or if you suspect data is stale.
                    </p>

                    <form action="{{ route('admin.settings.clear_cache') }}" method="POST">
                        @csrf
                        <button type="submit" class="aero-btn aero-btn-danger w-100">
                            <i class="fas fa-trash-alt me-2"></i> Clear System Cache
                        </button>
                        <small class="d-block text-center mt-2 text-muted">
                            Executes: <code>php artisan optimize:clear</code>
                        </small>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Modals (Moved outside main layout containers to prevent CSS transform flickering) -->
    @if(isset($googleSheets) && !$googleSheets->isEmpty())
        @foreach($googleSheets as $sheet)
            <div class="modal fade" id="editSheet{{ $sheet->id }}" tabindex="-1" aria-labelledby="editSheetLabel{{ $sheet->id }}" aria-hidden="true" data-bs-backdrop="static">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content" style="background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 16px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.5);">
                        <form action="{{ route('admin.settings.sheets.update', $sheet->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="modal-header" style="border-bottom: 1px solid var(--border-color); background: rgba(0,0,0,0.2); padding: 20px 24px;">
                                <h5 class="modal-title mb-0" id="editSheetLabel{{ $sheet->id }}" style="color: var(--text-primary); font-weight: 600;">
                                    <i class="fas fa-edit me-2" style="color: var(--accent-blue);"></i>Edit Sheet: {{ $sheet->title }}
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" style="filter: invert(1) grayscale(100%) brightness(200%); opacity: 0.8;"></button>
                            </div>
                            <div class="modal-body p-4">
                                <div class="mb-3 aero-form-group">
                                    <label class="aero-label">Title <span class="text-danger">*</span></label>
                                    <input type="text" name="title" class="aero-input" value="{{ $sheet->title }}" required>
                                </div>
                                <div class="mb-3 aero-form-group">
                                    <label class="aero-label">Icon Class</label>
                                    <input type="text" name="icon" class="aero-input" value="{{ $sheet->icon }}">
                                    <small class="text-muted d-block mt-1">FontAwesome icon class (e.g. fas fa-file-excel)</small>
                                </div>
                                <div class="mb-3 aero-form-group">
                                    <label class="aero-label">Google Sheet URL <span class="text-danger">*</span></label>
                                    <input type="url" name="url" class="aero-input" value="{{ $sheet->url }}" required>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3 aero-form-group">
                                        <label class="aero-label">Permission Type <span class="text-danger">*</span></label>
                                        <select name="permission_type" class="aero-select"
                                            id="edit_permission_{{ $sheet->id }}"
                                            onchange="toggleShiftField(this, 'edit_shift_field_{{ $sheet->id }}')">
                                            <option value="public" {{ $sheet->permission_type === 'public' ? 'selected' : '' }}>Public (Everyone)</option>
                                            <option value="shift_based" {{ $sheet->permission_type === 'shift_based' ? 'selected' : '' }}>Shift Based</option>
                                            <option value="admin_only" {{ $sheet->permission_type === 'admin_only' ? 'selected' : '' }}>Admin Only</option>
                                            <option value="specific_users" {{ $sheet->permission_type === 'specific_users' ? 'selected' : '' }}>Specific Users</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3 aero-form-group" id="edit_shift_field_{{ $sheet->id }}"
                                        style="{{ $sheet->permission_type !== 'shift_based' ? 'display:none;' : '' }}">
                                        <label class="aero-label">Shift</label>
                                        <select name="shift" class="aero-select">
                                            <option value="">Select Shift...</option>
                                            @foreach(\App\Models\GoogleSheet::getAvailableShifts() as $key => $label)
                                                <option value="{{ $key }}" {{ $sheet->shift === $key ? 'selected' : '' }}>
                                                    {{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3 aero-form-group" id="edit_users_field_{{ $sheet->id }}"
                                        style="{{ $sheet->permission_type !== 'specific_users' ? 'display:none;' : '' }}">
                                        <label class="aero-label">Select Users (Active Only)</label>
                                        <div class="user-search-wrapper">
                                            <i class="fas fa-search"></i>
                                            <input type="text" class="aero-input user-search-input" placeholder="Search users for this sheet..." 
                                                   onkeyup="filterUsers(this, 'edit_user_list_{{ $sheet->id }}')">
                                        </div>
                                        <div class="user-selection-list" id="edit_user_list_{{ $sheet->id }}">
                                            @php $assignedUserIds = $sheet->users->pluck('id')->toArray(); @endphp
                                            @foreach($users as $user)
                                                <label class="user-selection-item">
                                                    <input type="checkbox" name="user_ids[]" value="{{ $user->id }}" 
                                                        {{ in_array($user->id, $assignedUserIds) ? 'checked' : '' }}>
                                                    <div class="user-selection-info">
                                                        <span class="user-selection-name">{{ $user->name }}</span>
                                                        <span class="user-selection-email">{{ $user->email }}</span>
                                                    </div>
                                                </label>
                                            @endforeach
                                        </div>
                                        <small class="text-muted d-block mt-2"><i class="fas fa-info-circle me-1"></i>Existing assignments are preserved.</small>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer" style="border-top: 1px solid var(--border-color); padding: 16px 24px;">
                                <button type="button" class="aero-btn me-2" style="background: var(--bg-secondary); color: var(--text-primary); border: 1px solid var(--border-color);"
                                    data-bs-dismiss="modal"><i class="fas fa-times me-1"></i> Cancel</button>
                                <button type="submit" class="aero-btn aero-btn-primary"><i class="fas fa-save me-1"></i> Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    @endif
@endsection