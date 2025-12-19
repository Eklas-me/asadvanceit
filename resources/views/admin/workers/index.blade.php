@extends('layouts.dashboard')

@section('content')
    <div class="page-header fade-in">
        <h1 class="page-title">Manage Workers</h1>
        <div class="btn-wrapper">
            <a href="{{ route('admin.workers.create') }}" class="aero-btn aero-btn-primary">
                <i class="fa fa-plus"></i> Add Worker
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            @if (session('success'))
                <div class="aero-alert aero-alert-success">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="aero-alert aero-alert-danger">{{ session('error') }}</div>
            @endif

            @if (isset($md5Count) && $md5Count > 0)
                <div class="aero-alert aero-alert-warning mb-4">
                    <i class="fa fa-exclamation-triangle me-2"></i>
                    <strong>Attention Needed:</strong> {{ $md5Count }} active workers are still using legacy (MD5) passwords.
                    They will automatically be upgraded upon their next login.
                </div>
            @endif

            <!-- Worker Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="aero-card" style="border-left: 4px solid #22c55e;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-2" style="color: var(--text-muted);">Total Active Workers</h6>
                                <h3 class="mb-0">{{ $activeCount ?? 0 }}</h3>
                            </div>
                            <div class="icon-circle"
                                style="background: rgba(34, 197, 94, 0.1); width: 48px; height: 48px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                <i class="fa fa-users" style="color: #22c55e; font-size: 20px;"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="aero-card" style="border-left: 4px solid #eab308;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-2" style="color: var(--text-muted);">Suspended Workers</h6>
                                <h3 class="mb-0">{{ $suspendedCount ?? 0 }}</h3>
                            </div>
                            <div class="icon-circle"
                                style="background: rgba(234, 179, 8, 0.1); width: 48px; height: 48px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                <i class="fa fa-ban" style="color: #eab308; font-size: 20px;"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="aero-card" style="border-left: 4px solid #ef4444;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-2" style="color: var(--text-muted);">Rejected Workers</h6>
                                <h3 class="mb-0">{{ $rejectedCount ?? 0 }}</h3>
                            </div>
                            <div class="icon-circle"
                                style="background: rgba(239, 68, 68, 0.1); width: 48px; height: 48px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                <i class="fa fa-user-times" style="color: #ef4444; font-size: 20px;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="aero-card">
                <div class="aero-card-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <div class="d-flex align-items-center gap-4">
                        <a href="{{ route('admin.workers.index', ['role' => 'worker']) }}" class="aero-card-title mb-0"
                            style="text-decoration: none; cursor: pointer; {{ request('role', 'worker') != 'admin' ? 'color: var(--text-primary); border-bottom: 2px solid var(--primary);' : 'color: var(--text-muted);' }}">
                            Worker List
                        </a>
                        <a href="{{ route('admin.workers.index', ['role' => 'admin']) }}" class="aero-card-title mb-0"
                            style="text-decoration: none; cursor: pointer; {{ request('role') == 'admin' ? 'color: var(--text-primary); border-bottom: 2px solid var(--primary);' : 'color: var(--text-muted);' }}">
                            Admin List
                        </a>
                    </div>

                    {{-- Server-side Search Form --}}
                    <form action="{{ route('admin.workers.index') }}" method="GET"
                        style="position: relative; width: 300px;">
                        <input type="hidden" name="role" value="{{ request('role', 'worker') }}">
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Search {{ request('role') == 'admin' ? 'admins' : 'workers' }}..."
                            style="width: 100%; padding: 10px 70px 10px 15px; border: 1px solid var(--border-color); border-radius: 8px; background: var(--input-bg); color: var(--text-primary); font-size: 14px;">
                        <button type="submit"
                            style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--text-muted); cursor: pointer;">
                            <i class="fa fa-search"></i>
                        </button>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="aero-table">
                        <thead>
                            <tr>
                                <th>SN</th>
                                <th>Name</th>
                                <th>Profile Photo</th>
                                <th>Phone</th>
                                <th>Shift</th>
                                <th>Joined</th>
                                <th>Password Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="workerTableBody">
                            @include('admin.workers.partials.rows', ['workers' => $workers])
                        </tbody>
                        <!-- Shimmer Loading Rows (Hidden by default) -->
                        <tbody id="loadingRows" style="display: none;">
                            @for ($i = 0; $i < 3; $i++)
                                <tr>
                                    <td>
                                        <div class="shimmer-line" style="width: 30px;"></div>
                                    </td>
                                    <td>
                                        <div class="shimmer-line" style="width: 150px;"></div>
                                    </td>
                                    <td>
                                        <div class="shimmer-circle"></div>
                                    </td>
                                    <td>
                                        <div class="shimmer-line" style="width: 120px;"></div>
                                    </td>
                                    <td>
                                        <div class="shimmer-line" style="width: 80px;"></div>
                                    </td>
                                    <td>
                                        <div class="shimmer-line" style="width: 100px;"></div>
                                    </td>
                                    <td>
                                        <div class="shimmer-line" style="width: 100px;"></div>
                                    </td>
                                    <td>
                                        <div class="shimmer-line" style="width: 80px;"></div>
                                    </td>
                                </tr>
                            @endfor
                        </tbody>
                    </table>
                </div>

                <!-- Sentinel for Scroll Detection -->
                <div id="scrollSentinel" style="height: 20px;"></div>

                <!-- No Results Message (Hidden by default) -->
                <div id="noMoreData" style="text-align: center; padding: 20px; color: var(--text-muted); display: none;">
                    No more workers to load.
                </div>
            </div>
        </div>
    </div>
    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content aero-card">
                <div class="modal-header border-0">
                    <h5 class="modal-title">Delete Worker</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">Are you sure you want to delete <strong id="deleteWorkerName"></strong>?</p>

                    <form id="deleteForm" method="POST">
                        @csrf
                        @method('DELETE')

                        <div class="aero-form-group mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="delete_data" id="deleteDataCheck">
                                <label class="form-check-label text-white" for="deleteDataCheck">
                                    Delete all associated data (Tokens, Messages)?
                                </label>
                            </div>
                            <small class="text-white-50 ms-4 d-block">
                                If unchecked, data will be kept but unlinked (orphaned).
                            </small>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="aero-btn aero-btn-secondary"
                                data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="aero-btn"
                                style="background: #EF4444; color: white;">Delete</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Shimmer Animation CSS */
        .shimmer-line {
            height: 15px;
            background: #f6f7f8;
            background-image: linear-gradient(to right, #f6f7f8 0%, #edeef1 20%, #f6f7f8 40%, #f6f7f8 100%);
            background-repeat: no-repeat;
            background-size: 800px 104px;
            border-radius: 4px;
            animation-duration: 1s;
            animation-fill-mode: forwards;
            animation-iteration-count: infinite;
            animation-name: placeholderShimmer;
            animation-timing-function: linear;
            display: inline-block;
        }

        .shimmer-circle {
            height: 40px;
            width: 40px;
            border-radius: 50%;
            background: #f6f7f8;
            background-image: linear-gradient(to right, #f6f7f8 0%, #edeef1 20%, #f6f7f8 40%, #f6f7f8 100%);
            background-repeat: no-repeat;
            background-size: 800px 104px;
            animation-duration: 1s;
            animation-fill-mode: forwards;
            animation-iteration-count: infinite;
            animation-name: placeholderShimmer;
            animation-timing-function: linear;
            display: inline-block;
        }

        /* Dark mode adjustments */
        @media (prefers-color-scheme: dark) {

            .shimmer-line,
            .shimmer-circle {
                background: #2d2d2d;
                background-image: linear-gradient(to right, #2d2d2d 0%, #3d3d3d 20%, #2d2d2d 40%, #2d2d2d 100%);
            }
        }

        @keyframes placeholderShimmer {
            0% {
                background-position: -468px 0;
            }

            100% {
                background-position: 468px 0;
            }
        }
    </style>

    <script>
        function openDeleteModal(actionUrl, workerName) {
            const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
            document.getElementById('deleteForm').action = actionUrl;
            document.getElementById('deleteWorkerName').textContent = workerName;
            modal.show();
        }

        // Infinite Scroll Logic
        document.addEventListener('DOMContentLoaded', function () {
            let nextPageUrl = "{{ $workers->nextPageUrl() }}";
            let isLoading = false;
            const sentinel = document.getElementById('scrollSentinel');
            const loadingRows = document.getElementById('loadingRows');
            const tableBody = document.getElementById('workerTableBody');
            const noMoreData = document.getElementById('noMoreData');

            // If no more pages initially
            if (!nextPageUrl) {
                noMoreData.style.display = 'block';
                sentinel.style.display = 'none';
                return;
            }

            const observer = new IntersectionObserver((entries) => {
                if (entries[0].isIntersecting && !isLoading && nextPageUrl) {
                    loadMoreWorkers();
                }
            }, {
                rootMargin: '100px',
            });

            observer.observe(sentinel);

            function loadMoreWorkers() {
                isLoading = true;
                loadingRows.style.display = 'table-row-group';

                fetch(nextPageUrl, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                    .then(response => response.json())
                    .then(data => {
                        // Append new rows
                        tableBody.insertAdjacentHTML('beforeend', data.html);

                        nextPageUrl = data.next_page_url;

                        isLoading = false;
                        loadingRows.style.display = 'none';

                        if (!nextPageUrl) {
                            observer.unobserve(sentinel);
                            sentinel.style.display = 'none';
                            noMoreData.style.display = 'block';
                        }
                    })
                    .catch(error => {
                        console.error('Error loading more workers:', error);
                        isLoading = false;
                        loadingRows.style.display = 'none';
                    });
            }
        });
    </script>
@endsection