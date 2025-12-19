@extends('layouts.dashboard')

@section('content')
    <div class="page-header fade-in">
        <h1 class="page-title">{{ $title }}</h1>
    </div>

    <div class="spreadsheet-wrapper">
        <div class="spreadsheet-container">
            <iframe id="mySpreadsheet" src="{{ $url }}" frameborder="0" scrolling="no" loading="lazy"
                title="Embedded Google Sheet">
            </iframe>
            <div class="spinner" id="spinner"></div>
        </div>
        <button id="fullscreenBtn" aria-label="Toggle fullscreen spreadsheet">⛶</button>
    </div>

    <!-- CSS -->
    <style>
        .spreadsheet-wrapper {
            position: relative;
            max-width: 1200px;
            margin: 20px auto;
        }

        .spreadsheet-container {
            width: 100%;
            overflow: hidden;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
            position: relative;
            transition: all 0.3s ease;
            z-index: 1;
        }

        .spreadsheet-container.fullscreen {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            max-width: 100%;
            border-radius: 0;
            box-shadow: none;
            z-index: 9999;
        }

        .spreadsheet-container iframe {
            width: 100%;
            height: 80vh;
            border: none;
            transition: height 0.3s ease;
        }

        .spreadsheet-container.fullscreen iframe {
            height: 100%;
        }

        #fullscreenBtn {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 10000;
            background: rgba(0, 0, 0, 0.6);
            color: #fff;
            border: none;
            border-radius: 6px;
            padding: 6px 10px;
            cursor: pointer;
            font-size: 18px;
            transition: background 0.2s;
        }

        #fullscreenBtn:hover {
            background: rgba(0, 0, 0, 0.8);
        }

        /* Loading Spinner */
        .spinner {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            border: 4px solid #ccc;
            border-top: 4px solid #000;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            z-index: 20;
        }

        @keyframes spin {
            0% {
                transform: translate(-50%, -50%) rotate(0deg);
            }

            100% {
                transform: translate(-50%, -50%) rotate(360deg);
            }
        }

        /* Mobile-friendly adjustments */
        @media (max-width: 768px) {
            .spreadsheet-container iframe {
                height: 70vh;
            }
        }
    </style>

    <!-- JS -->
    <script>
        const btn = document.getElementById('fullscreenBtn');
        const container = document.querySelector('.spreadsheet-container');
        const iframe = document.getElementById('mySpreadsheet');
        const spinner = document.getElementById('spinner');

        let insideFullscreen = false;

        // Hide spinner when iframe loads
        iframe.addEventListener('load', () => {
            spinner.style.display = 'none';
        });

        // Toggle inside-fullscreen mode
        function toggleInsideFullscreen() {
            insideFullscreen = !insideFullscreen;
            if (insideFullscreen) {
                container.classList.add('fullscreen');
                btn.textContent = '❎';
            } else {
                container.classList.remove('fullscreen');
                btn.textContent = '⛶';
            }
        }

        // Button click
        btn.addEventListener('click', toggleInsideFullscreen);

        // Keyboard support: F = toggle fullscreen, Esc = exit
        document.addEventListener('keydown', (e) => {
            if (e.key.toLowerCase() === 'f') toggleInsideFullscreen();
            if (e.key === 'Escape' && insideFullscreen) toggleInsideFullscreen();
        });

        // Responsive height on window resize
        window.addEventListener('resize', () => {
            if (!insideFullscreen) {
                iframe.style.height = `${window.innerHeight * 0.8}px`;
            } else {
                iframe.style.height = '100%';
            }
        });
    </script>
@endsection