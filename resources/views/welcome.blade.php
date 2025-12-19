<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>As Advance It - Flexible Digital Marketing Careers</title>
    <link rel="icon" type="image/x-icon" href="{{ asset(getSetting('site_favicon', 'favicon.ico')) }}">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/theme.css') }}">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            overflow-x: hidden;
        }

        /* Animated Background */
        .animated-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            overflow: hidden;
        }

        .floating-shape {
            position: absolute;
            opacity: 0.15;
            animation: float 25s infinite ease-in-out;
            filter: blur(60px);
        }

        .shape-1 {
            top: 10%;
            left: 10%;
            width: 400px;
            height: 400px;
            background: linear-gradient(135deg, #3B82F6, #8B5CF6);
            border-radius: 50%;
            animation-delay: 0s;
        }

        .shape-2 {
            top: 60%;
            right: 10%;
            width: 300px;
            height: 300px;
            background: linear-gradient(135deg, #14B8A6, #3B82F6);
            border-radius: 30% 70% 70% 30% / 30% 30% 70% 70%;
            animation-delay: 8s;
        }

        .shape-3 {
            bottom: 10%;
            left: 50%;
            width: 350px;
            height: 350px;
            background: linear-gradient(135deg, #8B5CF6, #EC4899);
            border-radius: 63% 37% 54% 46% / 55% 48% 52% 45%;
            animation-delay: 16s;
        }

        @keyframes float {
            0%, 100% {
                transform: translate(0, 0) rotate(0deg) scale(1);
            }
            25% {
                transform: translate(50px, -50px) rotate(90deg) scale(1.1);
            }
            50% {
                transform: translate(-30px, 30px) rotate(180deg) scale(0.9);
            }
            75% {
                transform: translate(30px, 50px) rotate(270deg) scale(1.05);
            }
        }

        /* Fade In Animation */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(40px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInLeft {
            from {
                opacity: 0;
                transform: translateX(-40px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes fadeInRight {
            from {
                opacity: 0;
                transform: translateX(40px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .fade-in-up {
            animation: fadeInUp 1s ease-out forwards;
            opacity: 0;
        }

        .fade-in-left {
            animation: fadeInLeft 1s ease-out forwards;
            opacity: 0;
        }

        .fade-in-right {
            animation: fadeInRight 1s ease-out forwards;
            opacity: 0;
        }

        .delay-1 { animation-delay: 0.1s; }
        .delay-2 { animation-delay: 0.2s; }
        .delay-3 { animation-delay: 0.3s; }
        .delay-4 { animation-delay: 0.4s; }
        .delay-5 { animation-delay: 0.5s; }
        .delay-6 { animation-delay: 0.6s; }

        /* Pulse Animation */
        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.08);
            }
        }

        @keyframes glow {
            0%, 100% {
                box-shadow: 0 0 20px rgba(59, 130, 246, 0.3);
            }
            50% {
                box-shadow: 0 0 40px rgba(59, 130, 246, 0.6);
            }
        }

        /* Navigation */
        .landing-nav {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border-color);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .landing-nav.scrolled {
            box-shadow: var(--shadow-xl);
            background: rgba(255, 255, 255, 0.95);
        }

        [data-theme="dark"] .landing-nav {
            background: rgba(15, 17, 21, 0.85);
        }

        [data-theme="dark"] .landing-nav.scrolled {
            background: rgba(15, 17, 21, 0.95);
        }

        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .logo {
            font-size: 24px;
            font-weight: 800;
            background: var(--accent-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: pulse 3s infinite ease-in-out;
        }

        .nav-links {
            display: flex;
            gap: 32px;
            align-items: center;
        }

        .nav-links a {
            color: var(--text-secondary);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }

        .nav-links a::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--accent-gradient);
            transition: width 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .nav-links a:hover::after {
            width: 100%;
        }

        .nav-links a:hover {
            color: var(--accent-blue);
            transform: translateY(-2px);
        }

        .nav-cta {
            display: flex;
            gap: 12px;
        }

        .btn-primary {
            padding: 12px 24px;
            background: var(--accent-gradient);
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            text-decoration: none;
            display: inline-block;
            position: relative;
            overflow: hidden;
        }

        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
            transition: left 0.6s;
        }

        .btn-primary:hover::before {
            left: 100%;
        }

        .btn-primary:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 15px 30px rgba(59, 130, 246, 0.4);
        }

        .btn-secondary {
            padding: 12px 24px;
            background: transparent;
            color: var(--text-primary);
            border: 2px solid var(--border-color);
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            text-decoration: none;
            display: inline-block;
        }

        .btn-secondary:hover {
            background: var(--hover-bg);
            transform: translateY(-3px);
            border-color: var(--accent-blue);
        }

        /* Hero Section */
        .hero {
            padding: 160px 24px 100px;
            max-width: 1200px;
            margin: 0 auto;
            text-align: center;
            position: relative;
        }

        .hero h1 {
            font-size: 64px;
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 24px;
            background: var(--accent-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: -1px;
        }

        .hero p {
            font-size: 22px;
            color: var(--text-secondary);
            max-width: 750px;
            margin: 0 auto 50px;
            line-height: 1.7;
        }

        .hero-cta {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
        }

        /* Hero SVG Illustration */
        .hero-illustration {
            margin-top: 80px;
            animation: float 8s ease-in-out infinite;
        }

        /* Features Section */
        .features {
            padding: 100px 24px;
            background: var(--bg-secondary);
        }

        .section-title {
            text-align: center;
            font-size: 48px;
            font-weight: 700;
            margin-bottom: 20px;
        }

        .section-subtitle {
            text-align: center;
            font-size: 20px;
            color: var(--text-secondary);
            margin-bottom: 70px;
        }

        .features-grid {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 40px;
        }

        .feature-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 45px;
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: var(--accent-gradient);
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .feature-card:hover::before {
            transform: scaleX(1);
        }

        .feature-card::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(59, 130, 246, 0.1), transparent);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .feature-card:hover::after {
            width: 500px;
            height: 500px;
        }

        .feature-card:hover {
            transform: translateY(-15px) scale(1.02);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
        }

        .feature-icon {
            width: 70px;
            height: 70px;
            background: var(--accent-gradient);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 28px;
            transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
            animation: glow 3s infinite;
        }

        .feature-card:hover .feature-icon {
            transform: rotateY(360deg) scale(1.1);
        }

        .feature-icon i {
            font-size: 32px;
            color: white;
        }

        .feature-card h3 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 14px;
            position: relative;
            z-index: 1;
        }

        .feature-card p {
            color: var(--text-secondary);
            line-height: 1.7;
            position: relative;
            z-index: 1;
        }

        /* How It Works */
        .how-it-works {
            padding: 100px 24px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .steps {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 50px;
            margin-top: 70px;
        }

        .step {
            text-align: center;
            position: relative;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .step:hover {
            transform: translateY(-10px);
        }

        .step-number {
            width: 70px;
            height: 70px;
            background: var(--accent-gradient);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 28px;
            font-size: 28px;
            font-weight: 700;
            color: white;
            animation: pulse 3s infinite ease-in-out;
            box-shadow: 0 10px 30px rgba(59, 130, 246, 0.3);
        }

        .step h3 {
            font-size: 22px;
            font-weight: 600;
            margin-bottom: 14px;
        }

        .step p {
            color: var(--text-secondary);
            line-height: 1.7;
        }

        /* Stats Section */
        .stats {
            padding: 100px 24px;
            background: var(--accent-gradient);
            position: relative;
            overflow: hidden;
        }

        .stats::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 70%);
            animation: rotate 30s linear infinite;
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .stats-grid {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 50px;
            text-align: center;
            position: relative;
            z-index: 1;
        }

        .stat {
            transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .stat:hover {
            transform: scale(1.1);
        }

        .stat h2 {
            font-size: 56px;
            font-weight: 800;
            color: white;
            margin-bottom: 12px;
            text-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        }

        .stat p {
            font-size: 18px;
            color: rgba(255, 255, 255, 0.95);
            font-weight: 500;
        }

        /* Testimonials Section */
        .testimonials {
            padding: 100px 24px;
            background: var(--bg-secondary);
        }

        .testimonials-grid {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 40px;
            margin-top: 70px;
        }

        .testimonial-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 40px;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .testimonial-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
        }

        .testimonial-text {
            font-size: 16px;
            line-height: 1.8;
            color: var(--text-secondary);
            margin-bottom: 24px;
            font-style: italic;
        }

        .testimonial-author {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .author-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: var(--accent-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 20px;
        }

        .author-info h4 {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .author-info p {
            font-size: 14px;
            color: var(--text-muted);
        }

        /* Benefits Section */
        .benefits {
            padding: 100px 24px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .benefits-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: center;
            margin-top: 60px;
        }

        .benefits-list {
            list-style: none;
        }

        .benefit-item {
            display: flex;
            align-items: flex-start;
            gap: 20px;
            margin-bottom: 30px;
            transition: all 0.3s;
        }

        .benefit-item:hover {
            transform: translateX(10px);
        }

        .benefit-icon {
            width: 50px;
            height: 50px;
            background: var(--accent-gradient);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .benefit-icon i {
            color: white;
            font-size: 22px;
        }

        .benefit-text h4 {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .benefit-text p {
            color: var(--text-secondary);
            line-height: 1.6;
        }

        .benefits-visual {
            position: relative;
        }

        /* Footer */
        .footer {
            padding: 50px 24px;
            background: var(--card-bg);
            border-top: 1px solid var(--border-color);
            text-align: center;
        }

        .footer p {
            color: var(--text-secondary);
            font-size: 15px;
        }

        /* Mobile Menu */
        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            font-size: 24px;
            color: var(--text-primary);
            cursor: pointer;
        }

        @media (max-width: 768px) {
            .nav-links {
                display: none;
            }

            .mobile-menu-btn {
                display: block;
            }

            .hero h1 {
                font-size: 40px;
            }

            .hero p {
                font-size: 18px;
            }

            .section-title {
                font-size: 36px;
            }

            .features-grid, .testimonials-grid {
                grid-template-columns: 1fr;
            }

            .steps {
                grid-template-columns: 1fr;
            }

            .benefits-content {
                grid-template-columns: 1fr;
            }

            .floating-shape {
                display: none;
            }
        }
    </style>
</head>
<body data-theme="light">
    <!-- Animated Background -->
    <div class="animated-bg">
        <div class="floating-shape shape-1"></div>
        <div class="floating-shape shape-2"></div>
        <div class="floating-shape shape-3"></div>
    </div>

    <!-- Navigation -->
    <nav class="landing-nav" id="navbar">
        <div class="nav-container">
            <div class="logo">
                @if(getSetting('site_logo'))
                    <img src="{{ asset(getSetting('site_logo')) }}" alt="As Advance It" style="max-height: 50px;">
                @else
                    As Advance It
                @endif
            </div>
            <div class="nav-links">
                <a href="#features">Features</a>
                <a href="#how-it-works">Process</a>
                <a href="#testimonials">Testimonials</a>
                <a href="#benefits">Benefits</a>
            </div>
            <div class="nav-cta">
                @auth
                    <a href="{{ route('dashboard') }}" class="btn-primary">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="btn-secondary">Login</a>
                    <a href="{{ route('register') }}" class="btn-primary">Join Now</a>
                @endauth
            </div>
            <button class="mobile-menu-btn">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <h1 class="fade-in-up delay-1">Flexible Digital Marketing Careers</h1>
        <p class="fade-in-up delay-2">Join As Advance It and work on exciting digital marketing projects. Learn new skills, work flexibly, and earn on your own schedule with our part-time opportunities.</p>
        <div class="hero-cta fade-in-up delay-3">
            <a href="#how-it-works" class="btn-primary" style="padding: 16px 36px; font-size: 18px;">
                <i class="fas fa-info-circle me-2"></i>Learn More
            </a>
            <a href="#benefits" class="btn-secondary" style="padding: 16px 36px; font-size: 18px;">
                <i class="fas fa-star me-2"></i>See Benefits
            </a>
        </div>
        
        <!-- Hero Illustration -->
        <div class="hero-illustration fade-in-up delay-4">
            <svg width="600" height="400" viewBox="0 0 600 400" fill="none" xmlns="http://www.w3.org/2000/svg" style="max-width: 100%; height: auto;">
                <circle cx="300" cy="200" r="150" fill="url(#grad1)" opacity="0.25"/>
                <circle cx="250" cy="180" r="100" fill="url(#grad2)" opacity="0.35"/>
                <circle cx="350" cy="220" r="80" fill="url(#grad3)" opacity="0.35"/>
                <defs>
                    <linearGradient id="grad1" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" style="stop-color:#3B82F6;stop-opacity:1" />
                        <stop offset="100%" style="stop-color:#8B5CF6;stop-opacity:1" />
                    </linearGradient>
                    <linearGradient id="grad2" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" style="stop-color:#14B8A6;stop-opacity:1" />
                        <stop offset="100%" style="stop-color:#3B82F6;stop-opacity:1" />
                    </linearGradient>
                    <linearGradient id="grad3" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" style="stop-color:#8B5CF6;stop-opacity:1" />
                        <stop offset="100%" style="stop-color:#EC4899;stop-opacity:1" />
                    </linearGradient>
                </defs>
            </svg>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features" id="features">
        <h2 class="section-title fade-in-up">Why Choose As Advance It?</h2>
        <p class="section-subtitle fade-in-up delay-1">We provide everything you need to succeed in digital marketing</p>
        
        <div class="features-grid">
            <div class="feature-card fade-in-up delay-1">
                <div class="feature-icon">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <h3>Interview & Training</h3>
                <p>Pass our viva (interview) to join. Get 1-3 days of comprehensive training for every project. Performance-based continuation ensures quality work.</p>
            </div>

            <div class="feature-card fade-in-up delay-2">
                <div class="feature-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <h3>Flexible Schedule</h3>
                <p>Work when you want, where you want. Choose projects that fit your schedule and lifestyle perfectly.</p>
            </div>

            <div class="feature-card fade-in-up delay-3">
                <div class="feature-icon">
                    <i class="fas fa-briefcase"></i>
                </div>
                <h3>Diverse Projects</h3>
                <p>Work on various digital marketing projects including social media, content creation, SEO, and more.</p>
            </div>

            <div class="feature-card fade-in-up delay-4">
                <div class="feature-icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <h3>Competitive Pay</h3>
                <p>Earn competitive rates for your work. Get paid promptly for every completed project.</p>
            </div>

            <div class="feature-card fade-in-up delay-5">
                <div class="feature-icon">
                    <i class="fas fa-users"></i>
                </div>
                <h3>Supportive Community</h3>
                <p>Join a community of like-minded professionals. Get support, share experiences, and grow together.</p>
            </div>

            <div class="feature-card fade-in-up delay-6">
                <div class="feature-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h3>Career Growth</h3>
                <p>Build your portfolio, gain experience, and advance your career in digital marketing with real projects.</p>
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section class="how-it-works" id="how-it-works">
        <h2 class="section-title fade-in-up">How It Works</h2>
        <p class="section-subtitle fade-in-up delay-1">Our transparent hiring and onboarding process</p>

        <div class="steps">
            <div class="step fade-in-up delay-1">
                <div class="step-number">1</div>
                <h3>Apply & Interview</h3>
                <p>Create your account and apply. Pass our viva (interview) to confirm your position with us.</p>
            </div>

            <div class="step fade-in-up delay-2">
                <div class="step-number">2</div>
                <h3>Get Trained</h3>
                <p>Receive 1-3 days of comprehensive training when we get a new project. Learn the skills you need.</p>
            </div>

            <div class="step fade-in-up delay-3">
                <div class="step-number">3</div>
                <h3>Prove Your Skills</h3>
                <p>Apply what you learned during the training period. Show us you understand the work.</p>
            </div>

            <div class="step fade-in-up delay-4">
                <div class="step-number">4</div>
                <h3>Start Earning</h3>
                <p>Successfully complete your training and start working on projects. Get paid for your contributions.</p>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats">
        <div class="stats-grid">
            <div class="stat">
                <h2><span class="counter" data-target="500">0</span>+</h2>
                <p>Active Workers</p>
            </div>
            <div class="stat">
                <h2><span class="counter" data-target="1000">0</span>+</h2>
                <p>Projects Completed</p>
            </div>
            <div class="stat">
                <h2><span class="counter" data-target="95">0</span>%</h2>
                <p>Satisfaction Rate</p>
            </div>
            <div class="stat">
                <h2>24/7</h2>
                <p>Support Available</p>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="testimonials" id="testimonials">
        <h2 class="section-title fade-in-up">What Our Workers Say</h2>
        <p class="section-subtitle fade-in-up delay-1">Real experiences from our community members</p>
        
        <div class="testimonials-grid">
            <div class="testimonial-card fade-in-left delay-1">
                <p class="testimonial-text">"As Advance It gave me the flexibility I needed as a student. The training was excellent, and I learned so much about digital marketing!"</p>
                <div class="testimonial-author">
                    <div class="author-avatar">S</div>
                    <div class="author-info">
                        <h4>Sarah Ahmed</h4>
                        <p>Content Creator</p>
                    </div>
                </div>
            </div>

            <div class="testimonial-card fade-in-up delay-2">
                <p class="testimonial-text">"The projects are diverse and interesting. I've gained real-world experience that helped me build my portfolio and advance my career."</p>
                <div class="testimonial-author">
                    <div class="author-avatar">M</div>
                    <div class="author-info">
                        <h4>Mohammad Khan</h4>
                        <p>Social Media Specialist</p>
                    </div>
                </div>
            </div>

            <div class="testimonial-card fade-in-right delay-3">
                <p class="testimonial-text">"Great company culture and supportive team. The payment is always on time, and the work-life balance is perfect for me."</p>
                <div class="testimonial-author">
                    <div class="author-avatar">R</div>
                    <div class="author-info">
                        <h4>Rahima Begum</h4>
                        <p>SEO Analyst</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Benefits Section -->
    <section class="benefits" id="benefits">
        <h2 class="section-title fade-in-up">Additional Benefits</h2>
        <p class="section-subtitle fade-in-up delay-1">More reasons to join our growing community</p>
        
        <div class="benefits-content">
            <ul class="benefits-list fade-in-left delay-2">
                <li class="benefit-item">
                    <div class="benefit-icon">
                        <i class="fas fa-laptop-code"></i>
                    </div>
                    <div class="benefit-text">
                        <h4>Work From Anywhere</h4>
                        <p>All you need is a laptop and internet connection. Work from home, cafe, or anywhere you're comfortable.</p>
                    </div>
                </li>
                <li class="benefit-item">
                    <div class="benefit-icon">
                        <i class="fas fa-certificate"></i>
                    </div>
                    <div class="benefit-text">
                        <h4>Skill Certification</h4>
                        <p>Receive certificates for completed training programs to boost your professional credentials.</p>
                    </div>
                </li>
                <li class="benefit-item">
                    <div class="benefit-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <div class="benefit-text">
                        <h4>24/7 Support</h4>
                        <p>Our team is always available to help you with any questions or challenges you face.</p>
                    </div>
                </li>
                <li class="benefit-item">
                    <div class="benefit-icon">
                        <i class="fas fa-trophy"></i>
                    </div>
                    <div class="benefit-text">
                        <h4>Performance Rewards</h4>
                        <p>Top performers get bonuses, priority project access, and recognition in our community.</p>
                    </div>
                </li>
            </ul>
            
            <div class="benefits-visual fade-in-right delay-3">
                <svg width="500" height="500" viewBox="0 0 500 500" fill="none" xmlns="http://www.w3.org/2000/svg" style="max-width: 100%; height: auto;">
                    <circle cx="250" cy="250" r="200" fill="url(#benefitGrad1)" opacity="0.2"/>
                    <circle cx="250" cy="250" r="150" fill="url(#benefitGrad2)" opacity="0.3"/>
                    <circle cx="250" cy="250" r="100" fill="url(#benefitGrad3)" opacity="0.4"/>
                    <defs>
                        <linearGradient id="benefitGrad1" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" style="stop-color:#3B82F6;stop-opacity:1" />
                            <stop offset="100%" style="stop-color:#8B5CF6;stop-opacity:1" />
                        </linearGradient>
                        <linearGradient id="benefitGrad2" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" style="stop-color:#14B8A6;stop-opacity:1" />
                            <stop offset="100%" style="stop-color:#3B82F6;stop-opacity:1" />
                        </linearGradient>
                        <linearGradient id="benefitGrad3" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" style="stop-color:#8B5CF6;stop-opacity:1" />
                            <stop offset="100%" style="stop-color:#EC4899;stop-opacity:1" />
                        </linearGradient>
                    </defs>
                </svg>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <p>&copy; {{ date('Y') }} As Advance It. All rights reserved. | Building careers in digital marketing.</p>
    </footer>

    <!-- Scripts -->
    <script>
        // Theme Detection
        function getSystemTheme() {
            return window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        }

        (function() {
            const savedTheme = localStorage.getItem('theme');
            const systemTheme = getSystemTheme();
            const themeToApply = savedTheme || systemTheme;
            document.body.setAttribute('data-theme', themeToApply);
        })();

        if (window.matchMedia) {
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function(e) {
                if (!localStorage.getItem('theme')) {
                    const newTheme = e.matches ? 'dark' : 'light';
                    document.body.setAttribute('data-theme', newTheme);
                }
            });
        }

        // Smooth scroll
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });

        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.getElementById('navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // Counter Animation
        const counters = document.querySelectorAll('.counter');
        const speed = 200;
        let hasAnimated = false;

        const animateCounters = () => {
            if (hasAnimated) return;
            
            counters.forEach(counter => {
                const target = +counter.getAttribute('data-target');
                const increment = target / speed;
                let count = 0;

                const updateCount = () => {
                    count += increment;
                    if (count < target) {
                        counter.innerText = Math.ceil(count);
                        setTimeout(updateCount, 10);
                    } else {
                        counter.innerText = target;
                    }
                };

                updateCount();
            });
            
            hasAnimated = true;
        };

        // Intersection Observer for counter animation
        const statsSection = document.querySelector('.stats');
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    animateCounters();
                }
            });
        }, { threshold: 0.5 });

        if (statsSection) {
            observer.observe(statsSection);
        }

        // Intersection Observer for fade-in animations
        const fadeElements = document.querySelectorAll('.fade-in-up, .fade-in-left, .fade-in-right');
        const fadeObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translate(0, 0)';
                }
            });
        }, { threshold: 0.1 });

        fadeElements.forEach(el => {
            fadeObserver.observe(el);
        });
    </script>
</body>
</html>