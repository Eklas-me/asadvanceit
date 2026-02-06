@extends('layouts.app')

@section('content')
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="text-white">Active Agents</h2>
            <a href="{{ url('/home') }}" class="btn btn-secondary">Back to Dashboard</a>
        </div>

        <div class="row">
            @foreach($users as $user)
                <div class="col-md-4 mb-4">
                    <div class="card h-100 bg-dark text-white border-secondary">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="fas fa-desktop fa-3x text-primary"></i>
                            </div>
                            <h5 class="card-title">{{ $user->name }}</h5>
                            <p class="card-text text-muted">{{ $user->email }}</p>
                            <a href="{{ route('admin.monitoring.show', $user->id) }}" class="btn btn-primary w-100">
                                <i class="fas fa-eye me-2"></i> Watch Stream
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach

            @if($users->isEmpty())
                <div class="col-12 text-center text-white">
                    <p>No agents found.</p>
                </div>
            @endif
        </div>
    </div>
@endsection