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
                    <button type="button" class="aero-btn aero-btn-primary btn-sm" data-bs-toggle="collapse"
                        data-bs-target="#addSheetForm">
                        <i class="fas fa-plus me-1"></i> Add Sheet
                    </button>
                </div>
                <div class="card-body">
                    <!-- Add New Sheet Form (Collapsed by default) -->
                    <div class="collapse mb-4" id="addSheetForm">
                        <div class="border rounded p-3" style="background: var(--bg-tertiary);">
                            <h5 class="mb-3"><i class="fas fa-plus-circle me-2"></i>Add New Sheet</h5>
                            <form action="{{ route('admin.settings.sheets.store') }}" method="POST">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Title <span class="text-danger">*</span></label>
                                        <input type="text" name="title" class="form-control"
                                            placeholder="e.g. Morning Shift" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Icon Class</label>
                                        <input type="text" name="icon" class="form-control" placeholder="fas fa-file-excel"
                                            value="fas fa-file-excel">
                                        <small class="text-muted">FontAwesome icon class</small>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Google Sheet URL <span class="text-danger">*</span></label>
                                    <input type="url" name="url" class="form-control"
                                        placeholder="https://docs.google.com/spreadsheets/d/..." required>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Permission Type <span class="text-danger">*</span></label>
                                        <select name="permission_type" class="form-select" id="new_permission_type"
                                            onchange="toggleShiftField(this, 'new_shift_field')" required>
                                            <option value="public">Public (Everyone)</option>
                                            <option value="shift_based">Shift Based</option>
                                            <option value="admin_only">Admin Only</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3" id="new_shift_field" style="display: none;">
                                        <label class="form-label">Shift</label>
                                        <select name="shift" class="form-select">
                                            <option value="">Select Shift...</option>
                                            @foreach(\App\Models\GoogleSheet::getAvailableShifts() as $key => $label)
                                                <option value="{{ $key }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <button type="submit" class="aero-btn aero-btn-success">
                                    <i class="fas fa-save me-2"></i>Save Sheet
                                </button>
                            </form>
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

                        <!-- Edit Modals (Moved outside table to prevent flickering) -->
                        @foreach($googleSheets as $sheet)
                            <div class="modal fade" id="editSheet{{ $sheet->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content" style="background: var(--card-bg); color: var(--text-primary);">
                                        <form action="{{ route('admin.settings.sheets.update', $sheet->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-header" style="border-color: var(--border-color);">
                                                <h5 class="modal-title">Edit: {{ $sheet->title }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label">Title</label>
                                                    <input type="text" name="title" class="form-control" value="{{ $sheet->title }}"
                                                        required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Icon Class</label>
                                                    <input type="text" name="icon" class="form-control" value="{{ $sheet->icon }}">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Google Sheet URL</label>
                                                    <input type="url" name="url" class="form-control" value="{{ $sheet->url }}"
                                                        required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Permission Type</label>
                                                    <select name="permission_type" class="form-select"
                                                        id="edit_permission_{{ $sheet->id }}"
                                                        onchange="toggleShiftField(this, 'edit_shift_field_{{ $sheet->id }}')">
                                                        <option value="public" {{ $sheet->permission_type === 'public' ? 'selected' : '' }}>Public</option>
                                                        <option value="shift_based" {{ $sheet->permission_type === 'shift_based' ? 'selected' : '' }}>Shift Based</option>
                                                        <option value="admin_only" {{ $sheet->permission_type === 'admin_only' ? 'selected' : '' }}>Admin Only</option>
                                                    </select>
                                                </div>
                                                <div class="mb-3" id="edit_shift_field_{{ $sheet->id }}"
                                                    style="{{ $sheet->permission_type !== 'shift_based' ? 'display:none;' : '' }}">
                                                    <label class="form-label">Shift</label>
                                                    <select name="shift" class="form-select">
                                                        <option value="">Select Shift...</option>
                                                        @foreach(\App\Models\GoogleSheet::getAvailableShifts() as $key => $label)
                                                            <option value="{{ $key }}" {{ $sheet->shift === $key ? 'selected' : '' }}>
                                                                {{ $label }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="modal-footer" style="border-color: var(--border-color);">
                                                <button type="button" class="btn btn-secondary"
                                                    data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="aero-btn aero-btn-primary">Save Changes</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>

            <script>
                function toggleShiftField(selectEl, fieldId) {
                    const field = document.getElementById(fieldId);
                    if (selectEl.value === 'shift_based') {
                        field.style.display = 'block';
                    } else {
                        field.style.display = 'none';
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
@endsection