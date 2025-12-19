@extends('layouts.dashboard')

@section('content')
    <div class="page-header fade-in">
        <h1 class="page-title">Rejected Users</h1>
    </div>

    <div class="row">
        <div class="col-12">
            @if (session('success'))
                <div class="aero-alert aero-alert-success">{{ session('success') }}</div>
            @endif

            <div class="aero-card">
                <div class="aero-card-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <h2 class="aero-card-title">Rejected Users List</h2>
                    
                    {{-- Search Input --}}
                    <div style="position: relative; width: 300px;">
                        <input type="text" id="searchWorkers" placeholder="Search workers..." 
                            style="width: 100%; padding: 10px 70px 10px 15px; border: 1px solid var(--border-color); border-radius: 8px; background: var(--input-bg); color: var(--text-primary); font-size: 14px;">
                        <i class="fa fa-search" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
                        <button id="clearSearch" style="position: absolute; right: 40px; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--text-muted); cursor: pointer; display: none; font-size: 18px; padding: 5px;">×</button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="aero-table">
                        <thead>
                            <tr>
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
                            @foreach($rejectedUsers as $index => $user)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>{{ $user->phone }}</td>
                                    <td>{{ $user->shift }}</td>
                                    <td>
                                        @if($user->profile_photo)
                                            <img src="{{ asset('uploads/' . $user->profile_photo) }}"
                                                style="width: 40px; height: 40px; border-radius: 8px; object-fit: cover;">
                                        @else
                                            <span style="color: var(--text-muted);">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="aero-badge aero-badge-danger">{{ ucfirst($user->status) }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <form action="{{ route('admin.workers.activate', $user->id) }}" method="POST"
                                                style="display:inline;">
                                                @csrf
                                                <button type="submit" class="aero-btn aero-btn-sm"
                                                    style="background: #22c55e; color: white;" title="Activate">
                                                    <i class="fa fa-check"></i> Activate
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            @if($rejectedUsers->isEmpty())
                                <tr>
                                    <td colspan="8" class="text-center" style="padding: 60px 20px; color: var(--text-muted);">
                                        <i class="fas fa-user-times fa-4x mb-3" style="opacity: 0.2;"></i>
                                        <p>No rejected users.</p>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Search functionality
        const searchInput = document.getElementById('searchWorkers');
        const clearButton = document.getElementById('clearSearch');
        const tableRows = document.querySelectorAll('.aero-table tbody tr:not(#noResultsRow):not(:last-child)');

        searchInput.addEventListener('keyup', function () {
            const searchTerm = this.value.toLowerCase().trim();
            let visibleCount = 0;

            clearButton.style.display = searchTerm ? 'block' : 'none';

            tableRows.forEach(row => {
                const nameCell = row.querySelector('td:nth-child(2)');
                if (nameCell) {
                    const name = nameCell.textContent.toLowerCase();
                    if (name.includes(searchTerm)) {
                        row.style.display = '';
                        visibleCount++;
                    } else {
                        row.style.display = 'none';
                    }
                }
            });

            let noResultsRow = document.getElementById('noResultsRow');
            if (visibleCount === 0 && searchTerm) {
                if (!noResultsRow) {
                    noResultsRow = document.createElement('tr');
                    noResultsRow.id = 'noResultsRow';
                    noResultsRow.innerHTML = '<td colspan="8" style="text-align: center; padding: 40px; color: var(--text-muted);">No workers found matching "' + searchTerm + '"</td>';
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