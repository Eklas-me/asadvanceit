@extends('layouts.dashboard')

@section('content')
    <div class="page-header fade-in">
        <h1 class="page-title">Welcome, {{ auth()->user()->name }}</h1>
    </div>

    <div class="row g-3">
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="aero-stat-card fade-in">
                <div class="aero-stat-icon">
                    <i class="fas fa-calendar-day"></i>
                </div>
                <div class="aero-stat-label">Today</div>
                <div class="aero-stat-value">{{ $myTokenToday ?? '0' }}</div>
                <div class="aero-stat-change">
                    <i class="fas fa-key"></i> Tokens submitted
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-lg-3">
            <div class="aero-stat-card fade-in" style="animation-delay: 0.1s;">
                <div class="aero-stat-icon" style="color: var(--accent-purple);">
                    <i class="fas fa-history"></i>
                </div>
                <div class="aero-stat-label">Yesterday</div>
                <div class="aero-stat-value">{{ $myTokenYesterday ?? '0' }}</div>
                <div class="aero-stat-change">
                    <i class="fas fa-calendar-minus"></i> Previous day
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-lg-3">
            <div class="aero-stat-card fade-in" style="animation-delay: 0.2s;">
                <div class="aero-stat-icon" style="color: var(--accent-aqua);">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="aero-stat-label">This Month</div>
                <div class="aero-stat-value">{{ $myTokenMonth ?? '0' }}</div>
                <div class="aero-stat-change">
                    <i class="fas fa-chart-line"></i> Monthly total
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-lg-3">
            <div class="aero-stat-card fade-in" style="animation-delay: 0.3s;">
                <div class="aero-stat-icon" style="color: #F59E0B;">
                    <i class="fas fa-trophy"></i>
                </div>
                <div class="aero-stat-label">Lifetime</div>
                <div class="aero-stat-value">{{ $myTokenTotal ?? '0' }}</div>
                <div class="aero-stat-change">
                    <i class="fas fa-infinity"></i> All time
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="aero-card fade-in" style="animation-delay: 0.4s;">
                <div class="aero-card-header">
                    <h2 class="aero-card-title">
                        <i class="fas fa-chart-area me-2"></i>Performance Overview (Last 7 Days)
                    </h2>
                </div>
                <div style="padding: 20px; height: 350px;">
                    <canvas id="performanceChart"></canvas>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('performanceChart').getContext('2d');
            const chartData = @json($chartData);

            // Gradient for the chart line
            const gradient = ctx.createLinearGradient(0, 0, 0, 400);
            gradient.addColorStop(0, 'rgba(56, 189, 248, 0.5)'); // Light Blue
            gradient.addColorStop(1, 'rgba(56, 189, 248, 0.0)'); // Transparent

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: chartData.labels,
                    datasets: [{
                        label: 'Tokens Submitted',
                        data: chartData.data,
                        backgroundColor: gradient,
                        borderColor: '#38bdf8', // Light Blue
                        borderWidth: 2,
                        pointBackgroundColor: '#fff',
                        pointBorderColor: '#38bdf8',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                            backgroundColor: 'rgba(15, 23, 42, 0.9)',
                            titleColor: '#e2e8f0',
                            bodyColor: '#e2e8f0',
                            borderColor: 'rgba(255, 255, 255, 0.1)',
                            borderWidth: 1,
                            padding: 10,
                            displayColors: false,
                            callbacks: {
                                label: function (context) {
                                    return context.parsed.y + ' Tokens';
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false,
                                drawBorder: false
                            },
                            ticks: {
                                color: '#94a3b8'
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(148, 163, 184, 0.1)',
                                drawBorder: false
                            },
                            ticks: {
                                color: '#94a3b8',
                                stepSize: 1
                            }
                        }
                    },
                    interaction: {
                        mode: 'nearest',
                        axis: 'x',
                        intersect: false
                    }
                }
            });
        });
    </script>
@endpush