<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <link rel="icon" type="image/x-icon" href="{{ asset(getSetting('site_favicon', 'favicon.ico')) }}">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Aero Minimal Theme CSS -->
    <link rel="stylesheet" href="{{ asset('css/theme.css') }}">
    <link rel="stylesheet" href="{{ asset('css/layout-overrides.css') }}">

    @stack('styles')
</head>

<body data-theme="light">
    @yield('content')

    <script>
        // Theme Detection Function
        function getSystemTheme() {
            return window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        }

        // Apply theme immediately (before DOMContentLoaded to prevent flash)
        (function () {
            const savedTheme = localStorage.getItem('theme');
            const systemTheme = getSystemTheme();
            const themeToApply = savedTheme || systemTheme;
            document.body.setAttribute('data-theme', themeToApply);
        })();

        // Listen for system theme changes
        if (window.matchMedia) {
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function (e) {
                if (!localStorage.getItem('theme')) {
                    const newTheme = e.matches ? 'dark' : 'light';
                    document.body.setAttribute('data-theme', newTheme);
                }
            });
        }
    </script>

    @stack('scripts')
</body>

</html>