@foreach($workers as $index => $worker)
    <tr class="worker-row" data-id="{{ $worker->id }}">
        <td style="padding-left: 1.5rem;">
            <div class="form-check">
                <input type="checkbox" class="form-check-input custom-checkbox worker-checkbox" value="{{ $worker->id }}">
            </div>
        </td>
        <td>{{ $workers->firstItem() + $loop->index }}</td>
        <td>{{ $worker->name }}</td>
        <td>
            <img src="{{ $worker->profile_photo ? asset('storage/' . $worker->profile_photo) : asset('uploads/user.png') }}"
                alt="Profile" style="width: 40px; height: 40px; border-radius: 8px; object-fit: cover;">
        </td>
        <td>{{ $worker->phone }}</td>
        <td>{{ $worker->shift }}</td>
        <td>{{ $worker->created_at->format('M d, Y') }}</td>
        <td>
            @if($worker->needs_password_upgrade)
                <span class="badge bg-warning text-dark">Legacy (MD5)</span>
            @else
                <span class="badge bg-success">Updated</span>
            @endif
        </td>
        <td>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.workers.edit', $worker->id) }}" class="aero-btn aero-btn-sm" title="Edit">
                    <i class="fa fa-edit"></i>
                </a>

                @if($worker->status === 'active')
                    <form action="{{ route('admin.workers.suspend', $worker->id) }}" method="POST" style="display:inline;">
                        @csrf
                        <button type="submit" class="aero-btn aero-btn-sm" style="background: #eab308; color: white;"
                            title="Suspend">
                            <i class="fa fa-ban"></i>
                        </button>
                    </form>
                @elseif($worker->status === 'suspended' || $worker->status === 'rejected')
                    <form action="{{ route('admin.workers.activate', $worker->id) }}" method="POST" style="display:inline;">
                        @csrf
                        <button type="submit" class="aero-btn aero-btn-sm" style="background: #22c55e; color: white;"
                            title="Activate">
                            <i class="fa fa-check"></i>
                        </button>
                    </form>
                @endif

                <button type="button" class="aero-btn aero-btn-sm" style="background: #EF4444; color: white; border: none;"
                    onclick="openDeleteModal('{{ route('admin.workers.destroy', $worker->id) }}', '{{ $worker->name }}')">
                    <i class="fa fa-trash"></i>
                </button>
            </div>
        </td>
    </tr>
@endforeach