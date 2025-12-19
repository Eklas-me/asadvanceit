<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        // Admin sees only READ notifications (users who have read them)
        $query = Notification::with('user')
            ->where('status', 'read')
            ->orderBy('created_at', 'desc');
        $notifications = $query->paginate(20);

        // Handle AJAX request for infinite scroll
        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.notifications.list', compact('notifications'))->render(),
                'hasMore' => $notifications->hasMorePages()
            ]);
        }

        // Calculate stats
        $totalSent = Notification::count();
        $readCount = Notification::where('status', 'read')->count();
        $unreadCount = Notification::where('status', 'unread')->count();

        return view('admin.notifications.index', compact('notifications', 'totalSent', 'readCount', 'unreadCount'));
    }

    public function userIndex()
    {
        // User sees only their notifications
        $notifications = auth()->user()->notifications()->orderBy('created_at', 'desc')->paginate(20);
        return view('user.notifications.index', compact('notifications'));
    }

    public function create()
    {
        $users = \App\Models\User::where('role', 'user')->orderBy('name')->get();
        return view('admin.notifications.create', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required', // 'all' or integer ID
            'notification_title' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        if ($request->user_id === 'all') {
            // Send to ALL users
            $users = \App\Models\User::where('role', '!=', 'admin')->get();
            foreach ($users as $user) {
                Notification::create([
                    'user_id' => $user->id,
                    'title' => $request->notification_title,
                    'message' => $request->message,
                    'status' => 'unread',
                ]);
            }
        } else {
            // Send to SPECIFIC user
            Notification::create([
                'user_id' => $request->user_id,
                'title' => $request->notification_title,
                'message' => $request->message,
                'status' => 'unread',
            ]);
        }

        return redirect()->back()->with('success', 'Notification sent successfully!');
    }

    public function destroy(Notification $notification)
    {
        $notification->delete();
        return redirect()->back()->with('success', 'Notification deleted successfully!');
    }

    public function markAsRead(Notification $notification)
    {
        if ($notification->user_id == auth()->id()) {
            $notification->update(['status' => 'read']);
        }
        return redirect()->back();
    }
}
