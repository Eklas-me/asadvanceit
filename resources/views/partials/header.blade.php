<div class="main-header">
    <div class="navbar-header">
        <button class="btn btn-toggle toggle-sidebar sidenav-toggler d-lg-none"
            style="background: var(--hover-bg); border: none; padding: 8px 12px; border-radius: 8px;">
            <i class="fas fa-bars"></i>
        </button>

        @if(auth()->user()->role !== 'admin' && auth()->user()->shift)
            <div class="shift-badge">
                <i class="fas fa-clock me-2"></i>
                <span>{{ auth()->user()->shift }}</span>
            </div>
        @endif
    </div>

    <div class="navbar-nav">
        <!-- Theme Toggle -->
        <button class="theme-toggle-header" onclick="toggleTheme()" title="Toggle Theme">
            <i class="fas fa-moon theme-toggle-icon"></i>
        </button>

        <!-- Profile Dropdown -->
        <div class="dropdown">
            <a class="nav-profile" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                <div class="profile-avatar">
                    @if(auth()->user()->profile_photo)
                        <img src="{{ asset('uploads/' . auth()->user()->profile_photo) }}" alt="Profile">
                    @else
                        <span class="avatar-initial">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                    @endif
                </div>
                <div class="profile-info d-none d-md-block">
                    <span class="profile-name">{{ auth()->user()->name }}</span>
                    <span class="profile-role">{{ ucfirst(auth()->user()->role) }}</span>
                </div>
                <i class="fas fa-chevron-down dropdown-arrow"></i>
            </a>

            <ul class="dropdown-menu dropdown-menu-end">
                <li class="dropdown-header">
                    <div class="user-info">
                        <div class="user-name">{{ auth()->user()->name }}</div>
                        <div class="user-email">{{ auth()->user()->email }}</div>
                    </div>
                </li>
                <li>
                    <hr class="dropdown-divider">
                </li>
                <li>
                    <a class="dropdown-item" href="{{ route('profile.show') }}">
                        <i class="fas fa-user me-2"></i>
                        My Profile
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="#">
                        <i class="fas fa-cog me-2"></i>
                        Settings
                    </a>
                </li>
                <li>
                    <hr class="dropdown-divider">
                </li>
                <li>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="dropdown-item logout-item">
                            <i class="fas fa-sign-out-alt me-2"></i>
                            Logout
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</div>