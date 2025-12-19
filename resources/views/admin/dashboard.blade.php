@extends('layouts.dashboard')

@section('content')
    <div class="page-header fade-in">
        <h1 class="page-title">Admin Dashboard</h1>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="aero-stat-card fade-in">
                <div class="aero-stat-icon">
                    <i class="fas fa-key"></i>
                </div>
                <div class="aero-stat-label">Tokens Today</div>
                <div class="aero-stat-value">{{ $totalTokensToday ?? '0' }}</div>
                <div class="aero-stat-change">
                    <i class="fas fa-arrow-up"></i> Active count
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="aero-stat-card fade-in" style="animation-delay: 0.1s;">
                <div class="aero-stat-icon" style="color: var(--accent-purple);">
                    <i class="fas fa-users"></i>
                </div>
                <div class="aero-stat-label">Workers</div>
                <div class="aero-stat-value">{{ $totalWorkersToday }}</div>
                <div class="aero-stat-change">
                    <i class="fas fa-user-check"></i> Active today
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="aero-stat-card fade-in" style="animation-delay: 0.2s;">
                <div class="aero-stat-icon" style="color: var(--accent-aqua);">
                    <i class="fas fa-briefcase"></i>
                </div>
                <div class="aero-stat-label">Accounts</div>
                <div class="aero-stat-value">{{ $totalAccountToday }}</div>
                <div class="aero-stat-change">
                    <i class="fas fa-chart-line"></i> Total count
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="aero-stat-card fade-in" style="animation-delay: 0.3s;">
                <div class="aero-stat-icon" style="color: #F59E0B;">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="aero-stat-label">Active (30 min)</div>
                <div class="aero-stat-value">{{ $activeLast30Min ?? 0 }}</div>
                <div class="aero-stat-change">
                    <i class="fas fa-bolt"></i> Recent activity
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="aero-card fade-in" style="animation-delay: 0.4s;">
                <div class="aero-card-header"
                    style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                    <h2 class="aero-card-title" style="margin: 0;">
                        <i class="fas fa-users me-2"></i>All Workers Report
                    </h2>
                    <form id="filterForm" method="GET" action="{{ route('admin.dashboard') }}"
                        style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                        <input type="text" 
                               name="search_name" 
                               value="{{ $searchName ?? '' }}" 
                               placeholder="Search by name..."
                               class="form-control"
                               style="background: var(--card-bg); border: 1px solid var(--border-color); color: var(--text-primary); border-radius: 8px; padding: 8px 12px; font-size: 14px; width: 200px;">
                        <input type="datetime-local" name="start_date" value="{{ $startDate }}" class="form-control"
                            style="background: var(--card-bg); border: 1px solid var(--border-color); color: var(--text-primary); border-radius: 8px; padding: 8px 12px; font-size: 14px; width: auto;">
                        <input type="datetime-local" name="end_date" value="{{ $endDate }}" class="form-control"
                            style="background: var(--card-bg); border: 1px solid var(--border-color); color: var(--text-primary); border-radius: 8px; padding: 8px 12px; font-size: 14px; width: auto;">
                        <button type="submit" class="aero-btn aero-btn-sm"
                            style="background: var(--accent-blue); color: white; border: none; padding: 8px 20px;">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                    </form>
                </div>

                {{-- Top 3 Podium Leaderboard --}}
                @if($workersReport->count() >= 3)
                    <div class="leaderboard-container" style="padding: 100px 20px 50px 20px; background: var(--card-bg); border-radius: 16px; margin: 20px; border: 1px solid var(--border-color);">

                        <div class="leaderboard-flex" style="display: flex; justify-content: center; align-items: flex-end; gap: 50px; flex-wrap: wrap;">
                            @php
                                $topThree = $workersReport->take(3);
                                $ranks = ['1', '2', '3'];
                                $borderColors = [
                                    'linear-gradient(135deg, #FFD700, #FFA500)', // Gold gradient for 1st
                                    'linear-gradient(135deg, #E8E8E8, #C0C0C0)', // Silver gradient for 2nd
                                    'linear-gradient(135deg, #CD7F32, #8B4513)'  // Bronze gradient for 3rd
                                ];
                                $glowColors = [
                                    'rgba(255, 215, 0, 0.6)', // Gold glow
                                    'rgba(192, 192, 192, 0.6)', // Silver glow
                                    'rgba(205, 127, 50, 0.6)'  // Bronze glow
                                ];
                            @endphp

                            {{-- 2nd Place (Left) --}}
                            @if(isset($topThree[1]))
                                @php
                                    $worker = $topThree[1];
                                    $rank = 1;
                                @endphp
                                
                                <div style="flex: 0 0 auto; max-width: 200px; text-align: center; position: relative; margin-bottom: 20px;">
                                    {{-- Profile Picture --}}
                                    <div style="position: relative; display: inline-block; margin-bottom: 15px;">
                                        @if($worker->profile_photo)
                                        <img src="{{ asset('uploads/' . $worker->profile_photo) }}" 
                                             alt="{{ $worker->name }}"
                                             class="leaderboard-profile-2nd-3rd leaderboard-glow-silver"
                                             style="width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 4px solid #C0C0C0;">
                                        @else
                                            <div class="leaderboard-profile-2nd-3rd leaderboard-glow-silver" style="width: 120px; height: 120px; border-radius: 50%; background: linear-gradient(135deg, #4A5568, #2D3748); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 48px; border: 4px solid #C0C0C0;">
                                                {{ strtoupper(substr($worker->name, 0, 1)) }}
                                            </div>
                                        @endif
                                        
                                        {{-- Rank Badge --}}
                                        <div class="leaderboard-badge" style="position: absolute; bottom: -5px; left: 50%; transform: translateX(-50%); width: 40px; height: 40px; background: linear-gradient(135deg, #E8E8E8, #C0C0C0); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: 900; color: white; box-shadow: 0 4px 12px rgba(0,0,0,0.3); border: 3px solid var(--card-bg);">
                                            {{ $ranks[$rank] }}
                                        </div>
                                    </div>

                                    {{-- Name --}}
                                    <h4 class="leaderboard-name-2nd-3rd" style="font-size: 18px; font-weight: 600; color: var(--text-primary); margin: 15px 0 8px 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        {{ $worker->name }}
                                    </h4>

                                    {{-- Tokens Count --}}
                                    <div class="leaderboard-tokens-2nd-3rd" style="display: flex; align-items: center; justify-content: center; gap: 6px; font-size: 16px; font-weight: 600; color: var(--text-primary);">
                                        <span style="color: {{ $borderColors[$rank] }};">🪙</span>
                                        <span>{{ $worker->token_count }} Tokens</span>
                                    </div>
                                </div>
                            @endif

                            {{-- 1st Place (Center - Elevated) --}}
                            @if(isset($topThree[0]))
                                @php
                                    $worker = $topThree[0];
                                    $rank = 0;
                                @endphp
                                
                                <div class="leaderboard-1st-position" style="flex: 0 0 auto; max-width: 200px; text-align: center; position: relative; margin-top: -40px; margin-bottom: 40px;">
                                    {{-- Crown for 1st place --}}
                                    <div class="leaderboard-crown" style="position: absolute; top: -40px; left: 50%; transform: translateX(-50%); font-size: 60px; z-index: 10;">
                                        👑
                                    </div>

                                    {{-- Profile Picture --}}
                                    <div style="position: relative; display: inline-block; margin-bottom: 15px;">
                                        @if($worker->profile_photo)
                                            <img src="{{ asset('uploads/' . $worker->profile_photo) }}" 
                                                 alt="{{ $worker->name }}"
                                                 class="leaderboard-profile-1st leaderboard-glow-gold"
                                                 style="width: 140px; height: 140px; border-radius: 50%; object-fit: cover; border: 5px solid #FFD700;">
                                        @else
                                            <div class="leaderboard-profile-1st leaderboard-glow-gold" style="width: 140px; height: 140px; border-radius: 50%; background: linear-gradient(135deg, #4A5568, #2D3748); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 56px; border: 5px solid #FFD700;">
                                                {{ strtoupper(substr($worker->name, 0, 1)) }}
                                            </div>
                                        @endif
                                        
                                        {{-- Rank Badge --}}
                                        <div class="leaderboard-badge" style="position: absolute; bottom: -5px; left: 50%; transform: translateX(-50%); width: 40px; height: 40px; background: linear-gradient(135deg, #FFD700, #FFA500); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: 900; color: white; box-shadow: 0 4px 12px rgba(0,0,0,0.3); border: 3px solid var(--card-bg);">
                                            {{ $ranks[$rank] }}
                                        </div>
                                    </div>

                                    {{-- Name --}}
                                    <h4 class="leaderboard-name-1st" style="font-size: 22px; font-weight: 600; color: var(--text-primary); margin: 0 0 5px 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        {{ $worker->name }}
                                    </h4>

                                    {{-- Tokens Count --}}
                                    <div class="leaderboard-tokens-1st" style="display: flex; align-items: center; justify-content: center; gap: 6px; font-size: 18px; font-weight: 600; color: var(--text-primary);">
                                        <span style="color: {{ $borderColors[$rank] }};">🪙</span>
                                        <span>{{ $worker->token_count }} Tokens</span>
                                    </div>
                                </div>
                            @endif

                            {{-- 3rd Place (Right) --}}
                            @if(isset($topThree[2]))
                                @php
                                    $worker = $topThree[2];
                                    $rank = 2;
                                @endphp
                                
                                <div style="flex: 0 0 auto; max-width: 200px; text-align: center; position: relative; margin-bottom: 20px;">
                                    {{-- Profile Picture --}}
                                    <div style="position: relative; display: inline-block; margin-bottom: 15px;">
                                        @if($worker->profile_photo)
                                            <img src="{{ asset('uploads/' . $worker->profile_photo) }}" 
                                                 alt="{{ $worker->name }}"
                                                 class="leaderboard-profile-2nd-3rd leaderboard-glow-bronze"
                                                 style="width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 4px solid #CD7F32;">
                                        @else
                                            <div class="leaderboard-profile-2nd-3rd leaderboard-glow-bronze" style="width: 120px; height: 120px; border-radius: 50%; background: linear-gradient(135deg, #4A5568, #2D3748); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 48px; border: 4px solid #CD7F32;">
                                                {{ strtoupper(substr($worker->name, 0, 1)) }}
                                            </div>
                                        @endif
                                        
                                        {{-- Rank Badge --}}
                                        <div class="leaderboard-badge" style="position: absolute; bottom: -5px; left: 50%; transform: translateX(-50%); width: 40px; height: 40px; background: linear-gradient(135deg, #CD7F32, #8B4513); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: 900; color: white; box-shadow: 0 4px 12px rgba(0,0,0,0.3); border: 3px solid var(--card-bg);">
                                            {{ $ranks[$rank] }}
                                        </div>
                                    </div>

                                    {{-- Name --}}
                                    <h4 class="leaderboard-name-2nd-3rd" style="font-size: 18px; font-weight: 600; color: var(--text-primary); margin: 15px 0 8px 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        {{ $worker->name }}
                                    </h4>

                                    {{-- Tokens Count --}}
                                    <div class="leaderboard-tokens-2nd-3rd" style="display: flex; align-items: center; justify-content: center; gap: 6px; font-size: 16px; font-weight: 600; color: var(--text-primary);">
                                        <span style="color: {{ $borderColors[$rank] }};">🪙</span>
                                        <span>{{ $worker->token_count }} Tokens</span>
                                    </div>
                                </div>
                            @endif
                        </div>

                        {{-- Mobile Responsive Styles --}}
                        <style>
                            @media (max-width: 768px) {
                                /* Adjust container padding for mobile */
                                .leaderboard-container {
                                    padding: 50px 10px 30px 10px !important;
                                }
                                
                                /* Reduce gap between positions */
                                .leaderboard-flex {
                                    gap: 30px !important;
                                }
                                
                                /* Smaller profile pictures on mobile */
                                .leaderboard-profile-1st {
                                    width: 100px !important;
                                    height: 100px !important;
                                }
                                
                                .leaderboard-profile-2nd-3rd {
                                    width: 90px !important;
                                    height: 90px !important;
                                }
                                
                                /* Smaller crown */
                                .leaderboard-crown {
                                    font-size: 45px !important;
                                    top: -30px !important;
                                }
                                
                                /* Smaller rank badges */
                                .leaderboard-badge {
                                    width: 32px !important;
                                    height: 32px !important;
                                    font-size: 16px !important;
                                }
                                
                                /* Smaller text */
                                .leaderboard-name-1st {
                                    font-size: 18px !important;
                                }
                                
                                .leaderboard-name-2nd-3rd {
                                    font-size: 16px !important;
                                }
                                
                                .leaderboard-tokens-1st {
                                    font-size: 16px !important;
                                }
                                
                                .leaderboard-tokens-2nd-3rd {
                                    font-size: 14px !important;
                                }
                                
                                /* Adjust elevation for mobile */
                                .leaderboard-1st-position {
                                    margin-top: -30px !important;
                                    margin-bottom: 30px !important;
                                }
                            }

                            /* Glowing Animation */
                            @keyframes glow-gold {
                                0%, 100% {
                                    box-shadow: 0 0 20px rgba(255, 215, 0, 0.4), 0 0 40px rgba(255, 215, 0, 0.2);
                                }
                                50% {
                                    box-shadow: 0 0 30px rgba(255, 215, 0, 0.8), 0 0 60px rgba(255, 215, 0, 0.4);
                                }
                            }

                            @keyframes glow-silver {
                                0%, 100% {
                                    box-shadow: 0 0 20px rgba(192, 192, 192, 0.4), 0 0 40px rgba(192, 192, 192, 0.2);
                                }
                                50% {
                                    box-shadow: 0 0 30px rgba(192, 192, 192, 0.8), 0 0 60px rgba(192, 192, 192, 0.4);
                                }
                            }

                            @keyframes glow-bronze {
                                0%, 100% {
                                    box-shadow: 0 0 20px rgba(205, 127, 50, 0.4), 0 0 40px rgba(205, 127, 50, 0.2);
                                }
                                50% {
                                    box-shadow: 0 0 30px rgba(205, 127, 50, 0.8), 0 0 60px rgba(205, 127, 50, 0.4);
                                }
                            }

                            /* Apply animations */
                            .leaderboard-glow-gold {
                                animation: glow-gold 2s ease-in-out infinite;
                            }

                            .leaderboard-glow-silver {
                                animation: glow-silver 2s ease-in-out infinite;
                            }

                            .leaderboard-glow-bronze {
                                animation: glow-bronze 2s ease-in-out infinite;
                            }
                        </style>
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="aero-table">
                        <thead>
                            <tr>
                                <th
                                    style="text-align: left; padding-left: 20px; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px; color: var(--text-muted);">
                                    Workers Name</th>
                                <th
                                    style="text-align: center; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px; color: var(--text-muted);">
                                    Shift</th>
                                <th
                                    style="text-align: center; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px; color: var(--text-muted);">
                                    Tokens</th>
                                <th
                                    style="text-align: right; padding-right: 20px; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px; color: var(--text-muted);">
                                    Last Update</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $showLeaderboard = $workersReport->count() >= 3;
                                $tableWorkers = $showLeaderboard ? $workersReport->skip(3) : $workersReport;
                            @endphp
                            
                            @forelse($tableWorkers as $worker)
                                <tr>
                                    <td style="padding-left: 20px;">
                                        <div style="display: flex; align-items: center; gap: 12px;">
                                            @if($worker->profile_photo)
                                                <img src="{{ asset('uploads/' . $worker->profile_photo) }}" 
                                                     alt="{{ $worker->name }}"
                                                     style="width: 36px; height: 36px; border-radius: 50%; object-fit: cover; border: 2px solid var(--border-color);">
                                            @else
                                                <div style="width: 36px; height: 36px; border-radius: 50%; background: linear-gradient(135deg, var(--accent-blue), var(--accent-purple)); display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 14px;">
                                                    {{ strtoupper(substr($worker->name, 0, 1)) }}
                                                </div>
                                            @endif
                                            <span style="font-weight: 500;">{{ $worker->name }}</span>
                                        </div>
                                    </td>
                                    <td style="text-align: center;">
                                        @if($worker->shift)
                                            <span class="aero-badge aero-badge-info"
                                                style="font-size: 11px;">{{ $worker->shift }}</span>
                                        @else
                                            <span style="color: var(--text-muted); font-size: 12px;">-</span>
                                        @endif
                                    </td>
                                    <td style="text-align: center;">
                                        <span
                                            style="font-weight: 600; color: var(--accent-blue);">{{ $worker->token_count }}</span>
                                    </td>
                                    <td style="text-align: right; padding-right: 20px;">
                                        @if($worker->last_update)
                                            <span style="color: var(--text-secondary); font-size: 13px;">
                                                {{ \Carbon\Carbon::parse($worker->last_update)->format('m/d/Y h:i A') }}
                                            </span>
                                        @else
                                            <span style="color: var(--text-muted); font-size: 13px;">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center" style="padding: 60px 20px; color: var(--text-muted);">
                                        No data available for this range.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                {{-- Shimmer Loading Animation --}}
                <div id="shimmer-loader" style="display: none; padding: 20px;">
                    @for($i = 0; $i < 3; $i++)
                        <div class="shimmer-row" style="display: flex; align-items: center; gap: 12px; padding: 15px 20px; margin-bottom: 10px;">
                            <div class="shimmer shimmer-circle" style="width: 36px; height: 36px; border-radius: 50%;"></div>
                            <div style="flex: 1;">
                                <div class="shimmer shimmer-line" style="width: 150px; height: 16px; margin-bottom: 8px;"></div>
                                <div class="shimmer shimmer-line" style="width: 100px; height: 12px;"></div>
                            </div>
                            <div class="shimmer shimmer-line" style="width: 60px; height: 16px;"></div>
                            <div class="shimmer shimmer-line" style="width: 80px; height: 16px;"></div>
                            <div class="shimmer shimmer-line" style="width: 120px; height: 16px;"></div>
                        </div>
                    @endfor
                </div>
            </div>
        </div>
    </div>

    {{-- Shimmer Animation CSS --}}
    <style>
        .shimmer {
            background: linear-gradient(90deg, 
                var(--card-bg) 0%, 
                rgba(255, 255, 255, 0.1) 50%, 
                var(--card-bg) 100%);
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite;
            border-radius: 4px;
        }

        .shimmer-circle {
            border-radius: 50% !important;
        }

        .shimmer-line {
            border-radius: 4px;
        }

        @keyframes shimmer {
            0% {
                background-position: -200% 0;
            }
            100% {
                background-position: 200% 0;
            }
        }

        [data-theme="dark"] .shimmer {
            background: linear-gradient(90deg, 
                var(--card-bg) 0%, 
                rgba(255, 255, 255, 0.05) 50%, 
                var(--card-bg) 100%);
            background-size: 200% 100%;
        }
    </style>

    {{-- Infinite Scroll JavaScript --}}
    <script>
        let currentPage = 1;
        let isLoading = false;
        let hasMorePages = {{ $workersReport->hasMorePages() ? 'true' : 'false' }};
        
        const tableBody = document.querySelector('.aero-table tbody');
        const shimmerLoader = document.getElementById('shimmer-loader');
        const reportCard = document.querySelector('.aero-card');

        // Get current filter values
        function getFilters() {
            return {
                start_date: document.querySelector('input[name="start_date"]').value,
                end_date: document.querySelector('input[name="end_date"]').value,
                search_name: document.querySelector('input[name="search_name"]').value
            };
        }

        // Create worker row HTML
        function createWorkerRow(worker) {
            const profilePhoto = worker.profile_photo 
                ? `<img src="/uploads/${worker.profile_photo}" alt="${worker.name}" style="width: 36px; height: 36px; border-radius: 50%; object-fit: cover; border: 2px solid var(--border-color);">`
                : `<div style="width: 36px; height: 36px; border-radius: 50%; background: linear-gradient(135deg, var(--accent-blue), var(--accent-purple)); display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 14px;">${worker.name.charAt(0).toUpperCase()}</div>`;
            
            const shift = worker.shift 
                ? `<span class="aero-badge aero-badge-info" style="font-size: 11px;">${worker.shift}</span>`
                : `<span style="color: var(--text-muted); font-size: 12px;">-</span>`;
            
            const lastUpdate = worker.last_update 
                ? new Date(worker.last_update).toLocaleString('en-US', { month: '2-digit', day: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit', hour12: true })
                : '-';

            return `
                <tr>
                    <td style="padding-left: 20px;">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            ${profilePhoto}
                            <span style="font-weight: 500;">${worker.name}</span>
                        </div>
                    </td>
                    <td style="text-align: center;">
                        ${shift}
                    </td>
                    <td style="text-align: center;">
                        <span style="font-weight: 600; color: var(--accent-blue);">${worker.token_count}</span>
                    </td>
                    <td style="text-align: right; padding-right: 20px;">
                        <span style="color: var(--text-secondary); font-size: 13px;">${lastUpdate}</span>
                    </td>
                </tr>
            `;
        }

        // Load more workers
        async function loadMoreWorkers() {
            if (isLoading || !hasMorePages) return;

            isLoading = true;
            shimmerLoader.style.display = 'block';

            try {
                const filters = getFilters();
                const response = await fetch(`{{ route('admin.dashboard') }}?page=${currentPage + 1}&${new URLSearchParams(filters)}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const data = await response.json();

                // Remove empty state if exists
                const emptyRow = tableBody.querySelector('td[colspan="4"]');
                if (emptyRow) {
                    emptyRow.closest('tr').remove();
                }

                // Add new rows
                data.workers.forEach(worker => {
                    tableBody.insertAdjacentHTML('beforeend', createWorkerRow(worker));
                });

                currentPage++;
                hasMorePages = data.has_more;

            } catch (error) {
                console.error('Error loading workers:', error);
            } finally {
                isLoading = false;
                shimmerLoader.style.display = 'none';
            }
        }

        // Detect scroll near bottom
        function handleScroll() {
            const scrollPosition = window.innerHeight + window.scrollY;
            const documentHeight = document.documentElement.scrollHeight;
            
            // Load more when user is 300px from bottom
            if (scrollPosition >= documentHeight - 300) {
                loadMoreWorkers();
            }
        }

        // Attach scroll listener
        window.addEventListener('scroll', handleScroll);

        // Reset on filter change
        document.getElementById('filterForm').addEventListener('submit', function(e) {
            // Show loading modal
            document.getElementById('filterModal').style.display = 'block';
            
            currentPage = 1;
            hasMorePages = true;
        });
    </script>

    <!-- Filter Loading Modal -->
    <div id="filterModal"
        style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 9999; backdrop-filter: blur(5px);">
        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center;">
            <div class="spinner-border text-info" style="width: 3rem; height: 3rem; border-width: 4px;" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <h3 style="color: white; margin-top: 20px; text-shadow: 0 2px 10px rgba(0,0,0,0.5);">Searching Database...</h3>
            <p style="color: rgba(255,255,255,0.7); font-size: 14px;">This process runs securely on the optimized engine.</p>
        </div>
    </div>
@endsection