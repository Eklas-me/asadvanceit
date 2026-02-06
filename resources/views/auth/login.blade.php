@extends('layouts.app')

@push('styles')
    <style>
        :root {
            --primary-glow: #6366f1;
            --secondary-glow: #a855f7;
            --glass-bg: rgba(255, 255, 255, 0.03);
            --glass-border: rgba(255, 255, 255, 0.1);
        }

        body {
            background: #0b0e14;
            overflow-x: hidden;
            font-family: 'Poppins', sans-serif;
        }

        .auth-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            padding: 2rem;
            background: radial-gradient(circle at 50% 50%, #1e1b4b 0%, #0b0e14 100%);
        }

        .bg-glow {
            position: absolute;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.12) 0%, transparent 70%);
            border-radius: 50%;
            filter: blur(80px);
            z-index: 0;
            animation: float 20s infinite alternate cubic-bezier(0.45, 0, 0.55, 1);
        }

        .bg-glow-1 {
            top: -100px;
            left: -100px;
        }

        .bg-glow-2 {
            bottom: -100px;
            right: -100px;
            background: radial-gradient(circle, rgba(168, 85, 247, 0.12) 0%, transparent 70%);
        }

        @keyframes float {
            0% {
                transform: translate(0, 0);
            }

            100% {
                transform: translate(100px, 50px);
            }
        }

        .split-card {
            width: 100%;
            max-width: 480px;
            background: var(--glass-bg);
            backdrop-filter: blur(24px) saturate(180%);
            -webkit-backdrop-filter: blur(24px) saturate(180%);
            border: 1px solid var(--glass-border);
            border-radius: 32px;
            overflow: hidden;
            z-index: 10;
            box-shadow: 0 40px 80px -20px rgba(0, 0, 0, 0.6);
        }

        /* Row 1: Header/Status */
        .row-header {
            padding: 3.5rem 2.5rem 2.5rem;
            text-align: center;
            position: relative;
        }

        .brand-logo {
            font-size: 2.8rem;
            font-weight: 800;
            background: linear-gradient(135deg, #fff 0%, rgba(255, 255, 255, 0.6) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: -1.5px;
            margin-bottom: 0.2rem;
        }

        .brand-subtitle {
            color: rgba(255, 255, 255, 0.4);
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 2.5rem;
        }

        .status-area {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1rem;
        }

        .icon-box {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.2) 0%, rgba(168, 85, 247, 0.2) 100%);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1.8rem;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }

        .info-badge {
            background: rgba(255, 255, 255, 0.05);
            padding: 0.5rem 1.2rem;
            border-radius: 100px;
            border: 1px solid rgba(255, 255, 255, 0.08);
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.85rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .dot-live {
            width: 8px;
            height: 8px;
            background: #10b981;
            border-radius: 50%;
            box-shadow: 0 0 12px #10b981;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                opacity: 1;
            }

            50% {
                opacity: 0.4;
            }

            100% {
                opacity: 1;
            }
        }

        /* Row 2: Actions */
        .row-actions {
            background: rgba(255, 255, 255, 0.02);
            padding: 2.5rem;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
        }

        .action-summary {
            text-align: center;
            color: rgba(255, 255, 255, 0.5);
            font-size: 0.9rem;
            margin-bottom: 2rem;
            line-height: 1.5;
        }

        .download-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            color: white;
            padding: 1.2rem;
            border-radius: 20px;
            font-weight: 700;
            font-size: 1.1rem;
            text-decoration: none !important;
            transition: all 0.4s cubic-bezier(0.23, 1, 0.32, 1);
            box-shadow: 0 12px 24px -8px rgba(99, 102, 241, 0.5);
        }

        .download-btn:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 30px -10px rgba(99, 102, 241, 0.6);
            background: linear-gradient(135deg, #7c3aed 0%, #6366f1 100%);
            color: #fff;
        }

        .download-btn i {
            font-size: 1.3rem;
        }

        .footer-nav {
            margin-top: 2rem;
            display: flex;
            justify-content: center;
            gap: 1.5rem;
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.3);
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 500;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .nav-link:hover {
            color: #fff;
            transform: translateX(2px);
        }

        .alert-box {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: #fca5a5;
            padding: 1rem;
            border-radius: 16px;
            margin: 0 2.5rem 1rem;
            text-align: center;
            font-size: 0.85rem;
        }
    </style>
@endpush

@section('content')
    <div class="auth-wrapper">
        <div class="bg-glow bg-glow-1"></div>
        <div class="bg-glow bg-glow-2"></div>

        <div class="split-card fade-in">
            <!-- Row 1: Content/Identity -->
            <div class="row-header">
                <h1 class="brand-logo">Advanced IT</h1>
                <p class="brand-subtitle">System Gateway</p>

                <div class="status-area">
                    <div class="icon-box">
                        <i class="fas fa-desktop"></i>
                    </div>
                    <div class="info-badge">
                        <span class="dot-live"></span>
                        Secure Access Required
                    </div>
                </div>
            </div>

            @if ($errors->any())
                <div class="alert-box">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <!-- Row 2: Interaction/Actions -->
            <div class="row-actions">
                <form method="POST" action="{{ route('login') }}" style="margin-bottom: 2rem;">
                    @csrf

                    <div style="margin-bottom: 1.2rem;">
                        <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" required
                            autocomplete="email" autofocus placeholder="Email Address"
                            style="width: 100%; padding: 1rem; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; color: #fff; outline: none;">
                    </div>

                    <div style="margin-bottom: 1.5rem;">
                        <input id="password" type="password" class="form-control" name="password" required
                            autocomplete="current-password" placeholder="Password"
                            style="width: 100%; padding: 1rem; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; color: #fff; outline: none;">
                    </div>

                    <button type="submit"
                        style="width: 100%; padding: 1rem; background: var(--primary-glow); border: none; border-radius: 12px; color: #fff; font-weight: 600; cursor: pointer; transition: all 0.2s;">
                        Sign In
                    </button>
                </form>

                <div style="text-align: center; margin-bottom: 1rem; color: rgba(255,255,255,0.3); font-size: 0.8rem;">
                    — OR —
                </div>

                <a href="{{ route('app.download') }}" class="download-btn">
                    <i class="fas fa-download"></i>
                    <span>Download App</span>
                </a>

                <nav class="footer-nav">
                    <a href="{{ route('register') }}" class="nav-link">
                        <i class="fas fa-user-plus"></i> Register
                    </a>
                    <a href="#" class="nav-link">
                        <i class="fas fa-headset"></i> Support
                    </a>
                </nav>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const alert = document.querySelector('.aero-alert');
        if (alert) {
            setTimeout(() => {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            }, 5000);
        }
    </script>
@endpush