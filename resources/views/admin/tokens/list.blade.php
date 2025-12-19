@foreach($paginatedUsers as $user)
    <div class="col-md-6 user-card-item">
        <div class="aero-card">
            <div class="aero-card-header d-flex justify-content-between align-items-center">
                <h3 class="aero-card-title mb-0">
                    {{ $user->name }}
                    <span class="badge bg-secondary ms-2">{{ $user->liveTokens->count() }}</span>
                </h3>
                <button class="aero-btn aero-btn-sm btn-copy"
                    data-tokens="{{ $user->liveTokens->pluck('live_token')->implode("\n") }}">
                    <i class="fas fa-copy"></i> Copy
                </button>
            </div>
            <div style="max-height: 400px; overflow-y: auto;">
                <ul class="list-unstyled mb-0">
                    @foreach($user->liveTokens as $token)
                        <li style="padding: 10px 0; border-bottom: 1px solid var(--border-light); color: var(--text-primary);">
                            {{ $token->live_token }}
                            <span class="float-end" style="color: var(--text-muted); font-size: 12px;">
                                {{ $token->insert_time->format('H:i') }}
                            </span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endforeach