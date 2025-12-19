@extends('layouts.dashboard')

@section('content')
    <div class="page-header fade-in">
        <h1 class="page-title">
            <i class="fas fa-search me-2"></i>Duplicate Token Checker
        </h1>
    </div>

    <!-- Filter Form -->
    <div class="aero-card fade-in mb-4" style="animation-delay: 0.1s;">
        <form method="GET" action="{{ route('duplicate.checker') }}">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="aero-label">
                        <i class="fas fa-calendar-alt me-1"></i>Start Date & Time
                    </label>
                    <input type="datetime-local" name="start" class="aero-input" value="{{ $start }}">
                </div>
                <div class="col-md-4">
                    <label class="aero-label">
                        <i class="fas fa-calendar-check me-1"></i>End Date & Time
                    </label>
                    <input type="datetime-local" name="end" class="aero-input" value="{{ $end }}">
                </div>
                <div class="col-md-4">
                    <button type="submit" class="aero-btn aero-btn-primary w-100">
                        <i class="fas fa-search me-2"></i>Show Report
                    </button>
                </div>
            </div>
        </form>

        <div class="mt-3 d-flex gap-2">
            <a href="{{ route('duplicate.checker', ['preset' => 'today']) }}" class="aero-btn aero-btn-sm">
                <i class="fas fa-sun me-1"></i>Today
            </a>
            <a href="{{ route('duplicate.checker', ['preset' => 'yesterday']) }}" class="aero-btn aero-btn-sm">
                <i class="fas fa-moon me-1"></i>Yesterday
            </a>
        </div>
    </div>

    @if(!empty($start) && !empty($end))
        <!-- Date Range Display -->
        <div class="aero-alert aero-alert-info mb-4 fade-in" style="animation-delay: 0.2s;">
            <i class="fas fa-info-circle me-2"></i>
            <strong>Showing records between:</strong>
            <span class="text-gradient">{{ \Carbon\Carbon::parse($start)->format('M d, Y H:i') }}</span>
            →
            <span class="text-gradient">{{ \Carbon\Carbon::parse($end)->format('M d, Y H:i') }}</span>
        </div>

        <!-- Exact Duplicates -->
        <div class="aero-card fade-in mb-4" style="animation-delay: 0.3s;">
            <div class="aero-card-header">
                <h2 class="aero-card-title text-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>Exact Duplicate Tokens
                </h2>
            </div>

            <div class="table-responsive">
                <table class="aero-table">
                    <thead>
                        <tr>
                            <th>Token</th>
                            <th style="width: 100px;">Count</th>
                            <th>Users</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($exactDuplicates as $duplicate)
                            <tr>
                                <td style="word-break: break-all;">{{ $duplicate->normalized_token }}</td>
                                <td class="text-center">
                                    <span class="aero-badge aero-badge-danger">{{ $duplicate->total_submissions }}</span>
                                </td>
                                <td>{{ $duplicate->users }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted">
                                    <i class="fas fa-check-circle me-2"></i>No exact duplicates found in this range.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Near Duplicates -->
        <div class="aero-card fade-in" style="animation-delay: 0.4s;">
            <div class="aero-card-header">
                <h2 class="aero-card-title text-success">
                    <i class="fas fa-user-secret me-2"></i>Near Duplicate Tokens
                </h2>
            </div>

            <div class="table-responsive">
                <table class="aero-table">
                    <thead>
                        <tr>
                            <th>Matched Part</th>
                            <th style="width: 90px;">Total</th>
                            <th>Users</th>
                            <th>Links</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($nearDuplicates as $near)
                            <tr>
                                <td><code>{{ $near['matched_part'] }}</code></td>
                                <td class="text-center">
                                    <span class="aero-badge aero-badge-warning">{{ $near['total'] }}</span>
                                </td>
                                <td>{{ $near['users'] }}</td>
                                <td style="word-break: break-all;">
                                    @foreach($near['links'] as $link)
                                        <div class="mb-1">{{ $link }}</div>
                                    @endforeach
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">
                                    <i class="fas fa-check-circle me-2"></i>No near duplicates found in this range.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <style>
        code {
            background: var(--bg-tertiary);
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 13px;
            color: var(--accent-purple);
        }

        .text-danger {
            color: #EF4444 !important;
        }

        .text-success {
            color: var(--accent-aqua) !important;
        }
    </style>
@endsection