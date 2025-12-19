<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class ChatController extends Controller
{
    public function indexAdmin()
    {
        return view('admin.chat.index');
    }

    public function indexUser()
    {
        return view('user.chat.index');
    }

    public function fetchMessages($contactId, $contactType)
    {
        $currentUser = Auth::user();

        // Fetch messages between current user and contact
        $messages = Message::where(function ($q) use ($currentUser, $contactId) {
            $q->where('sender_id', $currentUser->id)
                ->where('receiver_id', $contactId);
        })->orWhere(function ($q) use ($currentUser, $contactId) {
            $q->where('sender_id', $contactId)
                ->where('receiver_id', $currentUser->id);
        })
            ->with('sender')
            ->orderBy('created_at', 'asc')
            ->get();

        // Mark messages from contact as read
        Message::where('sender_id', $contactId)
            ->where('receiver_id', $currentUser->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json($messages);
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|integer',
            'receiver_type' => 'required|string',
            'message' => 'required|string'
        ]);

        $currentUser = Auth::user();

        $message = Message::create([
            'sender_type' => $currentUser->role,
            'sender_id' => $currentUser->id,
            'receiver_type' => $request->receiver_type,
            'receiver_id' => $request->receiver_id,
            'message' => $request->message,
            'is_read' => false
        ]);

        return response()->json([
            'success' => true,
            'message' => $message->load('sender')
        ]);
    }

    public function fetchContacts()
    {
        $currentUser = Auth::user();
        $userId = $currentUser->id;

        // 1. Fetch All Contacts (for horizontal list - filtered by role)
        // Admin sees Users, Users see Admins
        $targetRole = $currentUser->role === 'admin' ? 'user' : 'admin';

        $allContacts = User::where('role', $targetRole)
            ->orderBy('name')
            ->get(['id', 'name', 'role', 'profile_photo', 'email', 'last_seen'])
            ->map(function ($user) {
                $user->is_online = $user->isOnline();
                return $user;
            });

        // 2. Fetch Recent Conversations (for vertical list)
        // Get users who have exchanged messages with current user, ordered by most recent message
        // using a subquery to find the latest message for each conversation

        $latestMessages = Message::select('id', 'sender_id', 'receiver_id', 'message', 'created_at', 'is_read')
            ->where(function ($q) use ($userId) {
                $q->where('sender_id', $userId)
                    ->orWhere('receiver_id', $userId);
            })
            ->orderBy('created_at', 'desc')
            ->get()
            ->unique(function ($item) use ($userId) {
                // Unique key for conversation: smaller_id-larger_id
                $p1 = $item->sender_id < $item->receiver_id ? $item->sender_id : $item->receiver_id;
                $p2 = $item->sender_id < $item->receiver_id ? $item->receiver_id : $item->sender_id;
                return $p1 . '-' . $p2;
            });

        $recentContacts = $latestMessages->map(function ($msg) use ($userId, $targetRole) {
            // Determine the "other" user in the conversation
            $otherUserId = $msg->sender_id == $userId ? $msg->receiver_id : $msg->sender_id;

            // Allow getting conversation even if role mismatch? 
            // For now, let's stick to the target role restriction or allow all if they validly chatted.
            // Usually we want to see anyone we chatted with.
            $contact = User::find($otherUserId);

            if (!$contact)
                return null; // User deleted?

            // Optional: Filter by role if strictly enforced
            if ($contact->role !== $targetRole)
                return null;

            $contact->last_message = $msg->message;
            $contact->last_message_time = $msg->created_at->diffForHumans(); // Or format as needed
            $contact->unread_count = Message::where('sender_id', $contact->id)
                ->where('receiver_id', $userId)
                ->where('is_read', false)
                ->count();
            $contact->is_online = $contact->isOnline();

            return $contact;
        })->filter()->values(); // Remove nulls and reindex

        return response()->json([
            'all_contacts' => $allContacts,
            'recent_conversations' => $recentContacts
        ]);
    }

    public function getUnreadCount()
    {
        $currentUser = Auth::user();

        $unreadCount = Message::where('receiver_id', $currentUser->id)
            ->where('is_read', false)
            ->count();

        return response()->json(['unread_count' => $unreadCount]);
    }
}
