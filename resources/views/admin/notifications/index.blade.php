@extends('layouts.dashboard')

@section('content')
    <div class="page-header fade-in">
        <h1 class="page-title">Manage Notifications</h1>
        <div class="btn-wrapper">
            <a href="{{ route('admin.notifications.create') }}" class="aero-btn aero-btn-primary">
                <i class="fas fa-plus"></i> Add Notification
            </a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-lg-4">
            <div class="aero-stat-card">
                <div class="aero-stat-icon" style="color: var(--accent-blue);">
                    <i class="fas fa-paper-plane"></i>
                </div>
                <div class="aero-stat-label">Total Sent</div>
                <div class="aero-stat-value">{{ $totalSent }}</div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-4">
            <div class="aero-stat-card">
                <div class="aero-stat-icon" style="color: var(--accent-green);">
                    <i class="fas fa-check-double"></i>
                </div>
                <div class="aero-stat-label">Read</div>
                <div class="aero-stat-value">{{ $readCount }}</div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-4">
            <div class="aero-stat-card">
                <div class="aero-stat-icon" style="color: var(--accent-orange);">
                    <i class="fas fa-envelope"></i>
                </div>
                <div class="aero-stat-label">Unread</div>
                <div class="aero-stat-value">{{ $unreadCount }}</div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            @if(session('success'))
                <div class="aero-alert aero-alert-success">{{ session('success') }}</div>
            @endif

            <div class="aero-card">
                <div class="aero-card-header">
                    <h2 class="aero-card-title">All Notifications</h2>
                </div>
                @if($notifications->count() > 0)
                    <div class="table-responsive">
                        <table class="aero-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Recipient</th>
                                    <th>Title</th>
                                    <th>Message</th>
                                    <th>Status</th>
                                    <th>Created At</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="notifications-tbody">
                                @include('admin.notifications.list')
                            </tbody>
                        </table>
                    </div>

                    <!-- Shimmer Skeleton Loader -->
                    <div id="shimmer-loader" style="display: none;">
                        <div class="table-responsive">
                            <table class="aero-table">
                                <tbody>
                                    @for($i = 0; $i < 5; $i++)
                                        <tr class="shimmer-row">
                                            <td>
                                                <div class="shimmer-box" style="width: 40px; height: 20px;"></div>
                                            </td>
                                            <td>
                                                <div class="shimmer-box" style="width: 150px; height: 20px;"></div>
                                            </td>
                                            <td>
                                                <div class="shimmer-box" style="width: 120px; height: 20px;"></div>
                                            </td>
                                            <td>
                                                <div class="shimmer-box" style="width: 200px; height: 20px;"></div>
                                            </td>
                                            <td>
                                                <div class="shimmer-box" style="width: 60px; height: 20px;"></div>
                                            </td>
                                            <td>
                                                <div class="shimmer-box" style="width: 80px; height: 20px;"></div>
                                            </td>
                                            <td>
                                                <div class="shimmer-box" style="width: 40px; height: 20px;"></div>
                                            </td>
                                        </tr>
                                    @endfor
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Scroll Sentinel -->
                    <div id="scroll-sentinel" style="height: 1px;"></div>
                @else
                    <div class="text-center" style="padding: 60px 20px;">
                        <i class="fas fa-bell-slash fa-4x mb-3" style="color: var(--text-muted); opacity: 0.2;"></i>
                        <p style="color: var(--text-muted);">No notifications found.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        /* Shimmer Animation */
        @keyframes shimmer {
            0% {
                background-position: -1000px 0;
            }

            100% {
                background-position: 1000px 0;
            }
        }

        .shimmer-box {
            background: linear-gradient(90deg,
                    var(--bg-tertiary) 0%,
                    var(--hover-bg) 50%,
                    var(--bg-tertiary) 100%);
            background-size: 1000px 100%;
            animation: shimmer 2s infinite linear;
            border-radius: 6px;
        }

        .shimmer-row {
            opacity: 0.6;
        }
    </style>
@endpush

@push('scripts')
    <script>
        let currentPage = 1;
        let isLoading = false;
        let hasMorePages = {{ $notifications->hasMorePages() ? 'true' : 'false' }};

        // Infinite Scroll Observer
        const sentinel = document.getElementById('scroll-sentinel');
        const shimmerLoader = document.getElementById('shimmer-loader');
        const tbody = document.getElementById('notifications-tbody');

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting && !isLoading && hasMorePages) {
                    loadMoreNotifications();
                }
            });
        }, {
            rootMargin: '200px'
        });

        if (sentinel) {
            observer.observe(sentinel);
        }

        async function loadMoreNotifications() {
            if (isLoading || !hasMorePages) return;

            isLoading = true;
            currentPage++;

            // Show shimmer
            shimmerLoader.style.display = 'block';

            try {
                const response = await fetch(`{{ route('admin.notifications.index') }}?page=${currentPage}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const data = await response.json();

                // Hide shimmer
                shimmerLoader.style.display = 'none';

                // Append new notifications
                tbody.insertAdjacentHTML('beforeend', data.html);

                // Update hasMore flag
                hasMorePages = data.hasMore;

                // If no more pages, stop observing
                if (!hasMorePages) {
                    observer.disconnect();
                }
            } catch (error) {
                console.error('Error loading notifications:', error);
                shimmerLoader.style.display = 'none';
            } finally {
                isLoading = false;
            }
        }
    </script>
@endpush