@foreach($notifications as $notification)
    <tr>
        <td>{{ $notification->id }}</td>
        <td>
            @if($notification->user)
                <div class="d-flex align-items-center">
                    <div class="avatar-sm me-2" style="width: 40px; height: 40px; flex-shrink: 0;">
                        @if($notification->user->profile_photo)
                            <img src="{{ asset('uploads/' . $notification->user->profile_photo) }}"
                                alt="{{ $notification->user->name }}" class="avatar-img rounded-circle"
                                style="width: 40px; height: 40px; object-fit: cover;">
                        @else
                            <span class="avatar-title rounded-circle border border-white"
                                style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; font-size: 16px; background: #6861CE; color: white;">
                                {{ strtoupper(substr($notification->user->name, 0, 1)) }}
                            </span>
                        @endif
                    </div>
                    {{ $notification->user->name }}
                </div>
            @else
                <span class="text-muted">Unknown User</span>
            @endif
        </td>
        <td>{{ $notification->title }}</td>
        <td>{{ Str::limit($notification->message, 40) }}</td>
        <td>
            <span class="aero-badge aero-badge-{{ $notification->status === 'unread' ? 'warning' : 'success' }}">
                {{ ucfirst($notification->status) }}
            </span>
        </td>
        <td>{{ $notification->created_at->format('M d, Y') }}</td>
        <td>
            <form action="{{ route('admin.notifications.destroy', $notification->id) }}" method="POST"
                onsubmit="return confirm('Are you sure?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="aero-btn aero-btn-sm" style="background: #EF4444; color: white; border: none;">
                    <i class="fas fa-trash"></i>
                </button>
            </form>
        </td>
    </tr>
@endforeach