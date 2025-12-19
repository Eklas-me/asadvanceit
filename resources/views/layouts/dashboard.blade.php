<!DOCTYPE html>
<html lang="en">

@include('partials.head')

<script>
    // Set theme IMMEDIATELY before body renders to prevent flash
    (function () {
        const savedTheme = localStorage.getItem('theme');
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        const theme = savedTheme || (prefersDark ? 'dark' : 'light');
        document.documentElement.setAttribute('data-theme', theme);
    })();
</script>

<body>
    <style>
        /* Mobile Sidebar Fix */
        @media screen and (max-width: 991px) {
            html.nav_open .sidebar {
                transform: translate3d(0, 0, 0) !important;
            }

            html.nav_open .main-header {
                z-index: 1001;
                /* Ensure header stays above sidebar if needed, or sidebar needs higher z-index */
            }

            /* Add overlay support if not present */
            html.nav_open .wrapper::after {
                content: '';
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.5);
                z-index: 1001;
            }

            html.nav_open .sidebar {
                z-index: 1002;
            }
        }
    </style>
    <div class="wrapper">
        @include('partials.sidebar')
        <div class="main-panel">
            @include('partials.header')
            <div class="container">
                <div class="page-inner">
                    @yield('content')
                </div>
            </div>
            @include('partials.footer')
        </div>
    </div>

    <!-- Core JS Files -->
    <script src="{{ asset('assets/js/core/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('assets/js/core/popper.min.js') }}"></script>
    <script src="{{ asset('assets/js/core/bootstrap.min.js') }}"></script>

    <!-- Kaiadmin JS -->
    <script src="{{ asset('assets/js/kaiadmin.min.js') }}"></script>

    <script>
        // Theme Detection and Toggle Function
        function getSystemTheme() {
            // Check if user's device prefers dark mode
            return window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        }

        function toggleTheme() {
            const root = document.documentElement;
            const currentTheme = root.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            const icons = document.querySelectorAll('.theme-toggle-icon');

            root.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);

            icons.forEach(icon => {
                icon.className = newTheme === 'dark' ? 'fas fa-sun theme-toggle-icon' : 'fas fa-moon theme-toggle-icon';
            });
        }

        // Load theme on page load (respects manual override, falls back to system preference)
        document.addEventListener('DOMContentLoaded', function () {
            const savedTheme = localStorage.getItem('theme'); // User's manual preference
            const systemTheme = getSystemTheme(); // Device's system preference
            const themeToApply = savedTheme || systemTheme; // Use saved theme if exists, otherwise use system

            const root = document.documentElement;
            const icons = document.querySelectorAll('.theme-toggle-icon');

            root.setAttribute('data-theme', themeToApply);
            icons.forEach(icon => {
                icon.className = themeToApply === 'dark' ? 'fas fa-sun theme-toggle-icon' : 'fas fa-moon theme-toggle-icon';
            });
        });

        // Listen for system theme changes (if user changes their device theme while app is open)
        if (window.matchMedia) {
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function (e) {
                // Only auto-switch if user hasn't manually set a preference
                if (!localStorage.getItem('theme')) {
                    const newTheme = e.matches ? 'dark' : 'light';
                    const root = document.documentElement;
                    const icons = document.querySelectorAll('.theme-toggle-icon');

                    root.setAttribute('data-theme', newTheme);
                    icons.forEach(icon => {
                        icon.className = newTheme === 'dark' ? 'fas fa-sun theme-toggle-icon' : 'fas fa-moon theme-toggle-icon';
                    });
                }
            });
        }
    </script>

    @stack('scripts')
</body>

</html>