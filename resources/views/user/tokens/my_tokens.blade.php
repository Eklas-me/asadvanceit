@extends('layouts.dashboard')

@section('content')
    <div class="page-header fade-in">
        <h1 class="page-title">My Tokens</h1>
    </div>

    <div class="row mb-3">
        <div class="col-12">
            @if(session('success'))
                <div class="aero-alert aero-alert-success">{{ session('success') }}</div>
            @endif

            <div class="aero-card">
                <form method="GET">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label">Select Date</label>
                            <input type="date" name="task_date" class="aero-input" value="{{ $date }}" required>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="aero-btn aero-btn-primary">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="aero-card">
                <div class="aero-card-header">
                    <h2 class="aero-card-title">
                        Tokens ({{ \Carbon\Carbon::parse($date)->addHours(7)->format('M j, Y, g:i a') }}
                        →
                        {{ \Carbon\Carbon::parse($date)->addDay()->addHours(7)->format('M j, Y, g:i a') }})
                    </h2>
                </div>
                @if($tokens->count() > 0)
                    <div class="table-responsive">
                        <table class="aero-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Token</th>
                                    <th>Inserted Time</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($tokens as $token)
                                    <tr>
                                        <td>{{ $token->id }}</td>
                                        <td>{{ $token->live_token }}</td>
                                        <td>{{ $token->insert_time->format('M j, Y, g:i a') }}</td>
                                        <td>
                                            <form action="{{ route('tokens.destroy', $token->id) }}" method="POST"
                                                onsubmit="return confirm('Are you sure?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="aero-btn aero-btn-sm"
                                                    style="background: #EF4444; color: white; border: none;">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center" style="padding: 60px 20px;">
                        <i class="fas fa-key fa-4x mb-3" style="color: var(--text-muted); opacity: 0.2;"></i>
                        <p style="color: var(--text-muted);">No tokens found for this date range.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection