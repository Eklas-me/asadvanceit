@extends('layouts.dashboard')

@section('content')
    <div class="page-header fade-in">
        <h1 class="page-title">
            <i class="fas fa-table me-2"></i>Google Sheets
        </h1>
    </div>

    <div class="spreadsheet-wrapper fade-in">
        <div class="spreadsheet-container">
            <iframe id="mySpreadsheet" src="{{ $sheetUrl }}" frameborder="0" scrolling="no" loading="lazy"
                title="Embedded Google Sheet">
            </iframe>
            <div class="spinner" id="spinner"></div>
        </div>
        <button id="fullscreenBtn" aria-label="Toggle fullscreen spreadsheet">⛶</button>
    </div>

    <style>
        .spreadsheet-wrapper {
            position: relative;
            max-width: 100%;
            margin: 0;
        }

        .spreadsheet-container {
            width: 100%;
            overflow: hidden;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            background: var(--card-bg);
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
            background: rgba(59, 130, 246, 0.9);
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 8px 12px;
            cursor: pointer;
            font-size: 18px;
            transition: all 0.2s;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        #fullscreenBtn:hover {
            background: rgba(59, 130, 246, 1);
            transform: scale(1.05);
            box-shadow: 0 6px 16px rgba(59, 130, 246, 0.4);
        }

        /* Loading Spinner */
        .spinner {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            border: 4px solid var(--border-color);
            border-top: 4px solid var(--accent-blue);
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