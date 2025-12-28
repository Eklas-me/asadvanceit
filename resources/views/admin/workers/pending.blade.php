@extends('layouts.dashboard')

@section('content')
    <div class="page-header fade-in">
        <h1 class="page-title">Pending Users</h1>
    </div>

    <div class="row">
        <div class="col-12">
            @if (session('success'))
                <div class="aero-alert aero-alert-success">{{ session('success') }}</div>
            @endif

            <div class="aero-card" style="position: relative;">

                <!-- Floating Bulk Actions Toolbar -->
                <div id="bulkActionsToolbar" class="bulk-toolbar" style="display: none;">
                    <div class="d-flex align-items-center justify-content-between w-100">
                        <div class="d-flex align-items-center">
                            <div class="count-badge me-3">
                                <span id="selectedCount">0</span>
                            </div>
                            <span class="text-white fw-medium">Items Selected</span>
                        </div>
                        <div class="d-flex gap-2">
                            <button onclick="submitBulkAction('activate')" class="aero-btn aero-btn-sm"
                                style="background: #22c55e; color: white;">
                                <i class="fas fa-check me-1"></i> Approve
                            </button>
                            <button onclick="submitBulkAction('reject')" class="aero-btn aero-btn-sm"
                                style="background: #ef4444; color: white;">
                                <i class="fas fa-times me-1"></i> Reject
                            </button>
                        </div>
                    </div>
                </div>

                <div class="aero-card-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <h2 class="aero-card-title">Pending Approvals</h2>

                    {{-- Search Input --}}
                    <div style="position: relative; width: 300px;">
                        <input type="text" id="searchWorkers" placeholder="Search workers..."
                            style="width: 100%; padding: 10px 70px 10px 15px; border: 1px solid var(--border-color); border-radius: 8px; background: var(--input-bg); color: var(--text-primary); font-size: 14px;">
                        <i class="fa fa-search"
                            style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
                        <button id="clearSearch"
                            style="position: absolute; right: 40px; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--text-muted); cursor: pointer; display: none; font-size: 18px; padding: 5px;">×</button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="aero-table">
                        <thead>
                            <tr>
                                <th style="width: 40px; padding-left: 1.5rem;">
                                    <div class="form-check">
                                        <input type="checkbox" id="selectAll" class="form-check-input custom-checkbox">
                                    </div>
                                </th>
                                <th>#</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Shift</th>
                                <th>Profile</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pendingUsers as $index => $user)
                                <tr class="worker-row">
                                    <td style="padding-left: 1.5rem;">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input custom-checkbox worker-checkbox"
                                                value="{{ $user->id }}">
                                        </div>
                                    </td>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>{{ $user->phone }}</td>
                                    <td>{{ $user->shift }}</td>
                                    <td>
                                    <td>
                                        @php
                                            $profilePhoto = $user->profile_photo;
                                            $defaultPhoto = asset('uploads/user.png'); // Or N/A text if preferred, but image is consistent
                                            $photoUrl = null;

                                            if ($profilePhoto) {
                                                if (file_exists(public_path('uploads/' . $profilePhoto))) {
                                                    $photoUrl = asset('uploads/' . $profilePhoto);
                                                } elseif (file_exists(public_path('storage/profile-photos/' . $profilePhoto))) {
                                                    $photoUrl = asset('storage/profile-photos/' . $profilePhoto);
                                                } elseif (file_exists(public_path('storage/' . $profilePhoto))) {
                                                    $photoUrl = asset('storage/' . $profilePhoto);
                                                }
                                            }
                                        @endphp

                                        @if($photoUrl)
                                            <img src="{{ $photoUrl }}"
                                                style="width: 40px; height: 40px; border-radius: 8px; object-fit: cover;">
                                        @else
                                            <span style="color: var(--text-muted);">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="aero-badge aero-badge-warning">{{ ucfirst($user->status) }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <a href="{{ route('admin.workers.approve', $user->id) }}"
                                                class="aero-btn aero-btn-sm"
                                                style="background: var(--accent-aqua); color: white; border: none;">
                                                <i class="fas fa-check"></i> Approve
                                            </a>
                                            <a href="javascript:void(0);"
                                                style="display: inline-block; padding: 5px 10px; background-color: #EF4444; color: white; border-radius: 4px; text-decoration: none; font-size: 12px; font-weight: bold; position: relative; z-index: 10;"
                                                onclick="event.preventDefault(); if(confirm('Reject this user?')) { window.location.href = '{{ route('admin.workers.reject', $user->id) }}'; }">
                                                <i class="fas fa-times"></i> Reject
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            @if($pendingUsers->isEmpty())
                                <tr>
                                    <td colspan="9" class="text-center" style="padding: 60px 20px; color: var(--text-muted);">
                                        <i class="fas fa-user-clock fa-4x mb-3" style="opacity: 0.2;"></i>
                                        <p>No pending users.</p>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Bulk Actions Toolbar Floating Style */
        .bulk-toolbar {
            position: fixed;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            width: 90%;
            max-width: 600px;
            background: rgba(30, 30, 40, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 12px 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            z-index: 9999;
            animation: slideUp 0.3s ease-out;
            display: flex;
            align-items: center;
        }

        @keyframes slideUp {
            from {
                transform: translate(-50%, 20px);
                opacity: 0;
            }

            to {
                transform: translate(-50%, 0);
                opacity: 1;
            }
        }

        .count-badge {
            background: var(--primary);
            color: white;
            padding: 2px 10px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 0.9rem;
        }

        /* Checkbox Styling Fix */
        .custom-checkbox {
            width: 18px;
            height: 18px;
            border-radius: 4px;
            border: 2px solid var(--border-color);
            background-color: transparent;
            cursor: pointer;
            position: relative;
        }

        .custom-checkbox:checked {
            background-color: var(--primary);
            border-color: var(--primary);
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20'%3e%3cpath fill='none' stroke='%23fff' stroke-linecap='round' stroke-linejoin='round' stroke-width='3' d='M6 10l3 3l6-6'/%3e%3c/svg%3e");
        }

        .form-check {
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 0;
            min-height: auto;
        }
    </style>

    <script>
        // Checkbox Logic
        const selectAll = document.getElementById('selectAll');
        const toolbar = document.getElementById('bulkActionsToolbar');
        const selectedCountSpan = document.getElementById('selectedCount');
        const workerTableBody = document.querySelector('tbody');

        function updateToolbar() {
            const checkedBoxes = document.querySelectorAll('.worker-checkbox:checked');
            const count = checkedBoxes.length;

            selectedCountSpan.textContent = count;
            if (count > 0) {
                toolbar.style.display = 'flex';
            } else {
                toolbar.style.display = 'none';
            }
        }

        // Event Delegation
        workerTableBody.addEventListener('change', function (e) {
            if (e.target.classList.contains('worker-checkbox')) {
                updateToolbar();
                if (!e.target.checked) selectAll.checked = false;
            }
        });

        if (selectAll) {
            selectAll.addEventListener('change', function () {
                const checkboxes = document.querySelectorAll('.worker-checkbox');
                const visibleCheckboxes = Array.from(checkboxes).filter(cb => cb.closest('tr').style.display !== 'none');

                visibleCheckboxes.forEach(cb => {
                    cb.checked = this.checked;
                });
                updateToolbar();
            });
        }

        function getSelectedIds() {
            return Array.from(document.querySelectorAll('.worker-checkbox:checked')).map(cb => cb.value);
        }

        function submitBulkAction(action) {
            const ids = getSelectedIds();
            if (ids.length === 0) return;

            if (!confirm(`Are you sure you want to ${action} ${ids.length} selected users?`)) return;

            fetch('{{ route("admin.workers.bulk_action") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ ids: ids, action: action })
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(err => console.error(err));
        }

        // Search functionality
        const searchInput = document.getElementById('searchWorkers');
        const clearButton = document.getElementById('clearSearch');
        const tableRows = document.querySelectorAll('.aero-table tbody tr:not(#noResultsRow):not(:last-child)');

        searchInput.addEventListener('keyup', function () {
            const searchTerm = this.value.toLowerCase().trim();
            let visibleCount = 0;

            clearButton.style.display = searchTerm ? 'block' : 'none';

            tableRows.forEach(row => {
                const nameCell = row.querySelector('td:nth-child(3)'); // Adjusted index for Name column (3rd child now due to checkbox)
                if (nameCell) {
                    const name = nameCell.textContent.toLowerCase();
                    if (name.includes(searchTerm)) {
                        row.style.display = '';
                        visibleCount++;
                    } else {
                        row.style.display = 'none';
                        // also uncheck if hidden to avoid accidental action
                        const cb = row.querySelector('.worker-checkbox');
                        if (cb) cb.checked = false;
                    }
                }
            });

            updateToolbar(); // Update count if items are hidden/unchecked

            let noResultsRow = document.getElementById('noResultsRow');
            if (visibleCount === 0 && searchTerm) {
                if (!noResultsRow) {
                    noResultsRow = document.createElement('tr');
                    noResultsRow.id = 'noResultsRow';
                    noResultsRow.innerHTML = '<td colspan="9" style="text-align: center; padding: 40px; color: var(--text-muted);">No workers found matching "' + searchTerm + '"</td>';
                    document.querySelector('.aero-table tbody').appendChild(noResultsRow);
                } else {
                    noResultsRow.querySelector('td').textContent = 'No workers found matching "' + searchTerm + '"';
                    noResultsRow.style.display = '';
                }
            } else if (noResultsRow) {
                noResultsRow.style.display = 'none';
            }
        });

        clearButton.addEventListener('click', function () {
            searchInput.value = '';
            searchInput.dispatchEvent(new Event('keyup'));
            searchInput.focus();
        });
    </script>
@endsection