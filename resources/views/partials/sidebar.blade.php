<div class="sidebar">
    <div class="logo-header">
        <a href="{{ auth()->user()->role === 'admin' ? route('admin.dashboard') : route('user.dashboard') }}"
            class="logo">
            @if(getSetting('site_logo'))
                <img src="{{ asset(getSetting('site_logo')) }}" alt="Logo" class="navbar-brand"
                    style="max-height: 40px; max-width: 180px;">
            @else
                <i class="fas fa-rocket" style="font-size: 20px;"></i>
                <span class="logo-text">Advance IT</span>
            @endif
        </a>
    </div>

    <div class="sidebar-wrapper">
        <ul class="nav">
            <li class="nav-item {{ request()->is('*/dashboard') ? 'active' : '' }}">
                <a href="{{ auth()->user()->role === 'admin' ? route('admin.dashboard') : route('user.dashboard') }}">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            @if(auth()->user()->role === 'admin')
                <li class="nav-item {{ request()->is('admin/tokens*') ? 'active' : '' }}">
                    <a href="{{ route('admin.tokens.index') }}">
                        <i class="fas fa-key"></i>
                        <span>All Tokens</span>
                    </a>
                </li>

                <li class="nav-item {{ request()->is('duplicate-checker*') ? 'active' : '' }}">
                    <a href="{{ route('duplicate.checker') }}">
                        <i class="fas fa-search"></i>
                        <span>Duplicate Checker</span>
                    </a>
                </li>



                <li
                    class="nav-item {{ request()->is('admin/workers*', 'admin/pending-users*', 'admin/suspended-users*', 'admin/rejected-users*') ? 'active' : '' }}">
                    <a href="#" data-bs-toggle="collapse" data-bs-target="#workersSubmenu"
                        aria-expanded="{{ request()->is('admin/workers*', 'admin/pending-users*', 'admin/suspended-users*', 'admin/rejected-users*') ? 'true' : 'false' }}">
                        <i class="fas fa-users"></i>
                        <span>Workers</span>
                        <span class="caret"><i class="fas fa-chevron-down"
                                style="font-size: 12px; margin-left: auto;"></i></span>
                    </a>
                    <div class="collapse {{ request()->is('admin/workers*', 'admin/pending-users*', 'admin/suspended-users*', 'admin/rejected-users*') ? 'show' : '' }}"
                        id="workersSubmenu">
                        <ul class="nav nav-collapse">
                            <li
                                class="nav-item {{ request()->is('admin/workers') && !request()->is('admin/workers/*') ? 'active' : '' }}">
                                <a href="{{ route('admin.workers.index') }}">
                                    <i class="fas fa-list"></i>
                                    <span>Active Workers</span>
                                </a>
                            </li>
                            <li class="nav-item {{ request()->is('admin/pending-users') ? 'active' : '' }}">
                                <a href="{{ route('admin.workers.pending') }}">
                                    <i class="fas fa-user-clock"></i>
                                    <span>Pending Users</span>
                                </a>
                            </li>
                            <li class="nav-item {{ request()->is('admin/suspended-users') ? 'active' : '' }}">
                                <a href="{{ route('admin.workers.suspended') }}">
                                    <i class="fas fa-user-slash"></i>
                                    <span>Suspended Users</span>
                                </a>
                            </li>
                            <li class="nav-item {{ request()->is('admin/rejected-users') ? 'active' : '' }}">
                                <a href="{{ route('admin.workers.rejected') }}">
                                    <i class="fas fa-user-times"></i>
                                    <span>Rejected Users</span>
                                </a>
                            </li>
                            <li class="nav-item {{ request()->is('admin/workers/create') ? 'active' : '' }}">
                                <a href="{{ route('admin.workers.create') }}">
                                    <i class="fas fa-user-plus"></i>
                                    <span>Add Worker</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
            @else
                <li class="nav-item {{ request()->is('tokens/my') ? 'active' : '' }}">
                    <a href="{{ route('tokens.my') }}">
                        <i class="fas fa-key"></i>
                        <span>My Tokens</span>
                    </a>
                </li>

                <li class="nav-item {{ request()->is('tokens/create') ? 'active' : '' }}">
                    <a href="{{ route('tokens.create') }}">
                        <i class="fas fa-plus-circle"></i>
                        <span>Add Tokens</span>
                    </a>
                </li>


            @endif

            <!-- Google Sheets Embedding -->
            <li class="nav-item {{ request()->is('sheets*') ? 'active' : '' }}">
                <a data-bs-toggle="collapse" href="#sheets">
                    <i class="fas fa-file-excel"></i>
                    <span>Sheets</span>
                    <span class="caret"><i class="fas fa-chevron-down"
                            style="font-size: 12px; margin-left: auto;"></i></span>
                </a>
                <div class="collapse {{ request()->is('sheets*') ? 'show' : '' }}" id="sheets">
                    <ul class="nav nav-collapse">
                        @php
                            $visibleSheets = \App\Http\Controllers\SheetsController::getVisibleSheets();
                            $user = auth()->user();
                            $iconMap = [
                                'facebook' => 'fab fa-facebook',
                                'morning_8_hours' => 'fas fa-sun',
                                'morning_8_hours_female' => 'fas fa-female',
                                'evening_8_hours' => 'fas fa-cloud-sun',
                                'night_8_hours' => 'fas fa-moon',
                                'day_12_hours' => 'fas fa-sun',
                                'night_12_hours' => 'fas fa-moon',
                            ];
                        @endphp

                        @foreach($visibleSheets as $slug => $config)
                            @php
                                $hasAccess = false;
                                if ($user->role === 'admin' || !empty($config['public'])) {
                                    $hasAccess = true;
                                } elseif (isset($config['shift']) && $user->shift === $config['shift']) {
                                    $hasAccess = true;
                                }
                            @endphp

                            @if($hasAccess)
                                <li class="nav-item {{ request()->is('sheets/' . $slug) ? 'active' : '' }}">
                                    <a href="{{ route('sheets.show', $slug) }}">
                                        <i class="{{ $iconMap[$slug] ?? 'fas fa-file-excel' }}"></i>
                                        <span class="sub-item">{{ $config['title'] }}</span>
                                    </a>
                                </li>
                            @endif
                        @endforeach

                        @if($user->role !== 'admin' && empty($visibleSheets))
                            <li><span class="sub-item text-danger" style="padding-left: 20px;">No sheets available!</span>
                            </li>
                        @endif
                    </ul>
                </div>
            </li>

            @php
                $unreadCount = auth()->user()->notifications()->where('status', 'unread')->count();
            @endphp
            <li class="nav-item {{ request()->is('*/notifications*') ? 'active' : '' }}">
                <a
                    href="{{ auth()->user()->role === 'admin' ? route('admin.notifications.index') : route('user.notifications.index') }}">
                    <i class="fas fa-bell"></i>
                    <span>Notifications</span>
                    @if($unreadCount > 0)
                        <span class="badge badge-danger" style="margin-left: auto;">{{ $unreadCount }}</span>
                    @endif
                </a>
            </li>

            @if(auth()->user()->role === 'admin')
                <li class="nav-item {{ request()->is('admin/settings*') ? 'active' : '' }}">
                    <a href="{{ route('admin.settings.index') }}">
                        <i class="fas fa-cogs"></i>
                        <span>Settings</span>
                    </a>
                </li>
            @endif
        </ul>

        <!-- Chat at bottom -->
        <ul class="nav nav-bottom">
            <li class="nav-item {{ request()->is('*/chat*') ? 'active' : '' }}">
                <a href="{{ auth()->user()->role === 'admin' ? route('admin.chat') : route('user.chat') }}">
                    <i class="fas fa-comments"></i>
                    <span>Chat</span>
                    <span class="badge badge-danger" id="chatUnreadBadge" style="display: none;">0</span>
                </a>
            </li>
        </ul>
    </div>
</div>

<script>
    // Chat unread count polling
    let chatUnreadInterval;

    function updateChatUnreadCount() {
        fetch('{{ route("chat.unread_count") }}')
            .then(response => response.json())
            .then(data => {
                const badge = document.getElementById('chatUnreadBadge');
                if (badge) {
                    const count = data.unread_count || 0;
                    if (count > 0) {
                        badge.textContent = count > 99 ? '99+' : count;
                        badge.style.display = 'inline-block';
                    } else {
                        badge.style.display = 'none';
                    }
                }
            })
            .catch(error => console.error('Error fetching unread count:', error));
    }

    // Initial load
    if (document.getElementById('chatUnreadBadge')) {
        updateChatUnreadCount();
        // Poll every 30 seconds
        chatUnreadInterval = setInterval(updateChatUnreadCount, 30000);
    }
</script>