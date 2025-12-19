<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\User;

class WorkerController extends Controller
{
    public function index(Request $request)
    {
        // Show only active users in Manage Workers
        // Suspended and rejected users have their own sections now

        $query = \App\Models\User::where('status', 'active');

        // Determine the role to filter by (default: 'worker')
        $role = $request->input('role', 'worker');

        // Apply role filter
        if ($role === 'admin') {
            $query->where('role', 'admin');
        } else {
            // Default: Show users who are NOT admins (or have no role set)
            $query->where(function ($q) {
                $q->where('role', '!=', 'admin')
                    ->orWhereNull('role');
            });
        }

        // Server-side search
        if ($request->has('search') && $request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $workers = $query->orderBy('created_at', 'desc')->paginate(50);

        // Ensure the 'role' parameter is preserved in pagination links (for infinite scroll)
        $workers->appends(['role' => $role]);

        if ($request->ajax()) {
            $view = view('admin.workers.partials.rows', compact('workers'))->render();
            return response()->json(['html' => $view, 'next_page_url' => $workers->nextPageUrl()]);
        }

        // Cache the counts for 30 minutes to improve performance
        $counts = Cache::remember('admin_worker_counts', 1800, function () {
            return [
                'md5' => \App\Models\User::where('needs_password_upgrade', true)->where('status', 'active')->count(),
                'active' => \App\Models\User::where('status', 'active')->count(),
                'suspended' => \App\Models\User::where('status', 'suspended')->count(),
                'rejected' => \App\Models\User::where('status', 'rejected')->count(),
            ];
        });

        $md5Count = $counts['md5'];
        $activeCount = $counts['active'];
        $suspendedCount = $counts['suspended'];
        $rejectedCount = $counts['rejected'];

        return view('admin.workers.index', compact('workers', 'md5Count', 'activeCount', 'suspendedCount', 'rejectedCount'));
    }

    public function create()
    {
        return view('admin.workers.create');
    }

    public function store(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'shift' => 'required|string',
            'role' => 'required|in:admin,user',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $photoName = null;
        if ($request->hasFile('profile_photo')) {
            $photoName = time() . '_' . $request->file('profile_photo')->getClientOriginalName();
            $request->file('profile_photo')->move(public_path('uploads'), $photoName);
        }

        \App\Models\User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => \Illuminate\Support\Facades\Hash::make($request->password),
            'phone' => $request->phone,
            'shift' => $request->shift,
            'role' => $request->role,
            'profile_photo' => $photoName,
            'status' => 'active', // Admin created users are active by default
        ]);

        return redirect()->route('admin.workers.index')->with('success', 'Worker added successfully!');
    }

    public function edit($id)
    {
        $worker = \App\Models\User::findOrFail($id);
        return view('admin.workers.edit', compact('worker'));
    }

    public function update(\Illuminate\Http\Request $request, $id)
    {
        $worker = \App\Models\User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'shift' => 'required|string',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('profile_photo')) {
            $photoName = time() . '_' . $request->file('profile_photo')->getClientOriginalName();
            $request->file('profile_photo')->move(public_path('uploads'), $photoName);
            $worker->profile_photo = $photoName;
        }

        $worker->name = $request->name;
        $worker->email = $request->email;
        $worker->phone = $request->phone;
        $worker->shift = $request->shift;
        $worker->save();

        return redirect()->route('admin.workers.index')->with('success', 'Worker updated successfully!');
    }

    public function destroy(Request $request, $id)
    {
        $worker = \App\Models\User::findOrFail($id);

        // Prevent deletion of core admin
        if ($worker->isCoreAdmin()) {
            return redirect()->back()->with('error', '🔒 Core admin cannot be deleted! This account is protected.');
        }

        if ($request->input('delete_data') === 'on') {
            // Delete all associated data (Tokens, Messages)
            // LiveTokens cascade by default on user delete if set up in migration.
            // Messages might need manual deletion if not set to cascade.
            // Assuming we want to be thorough:
            \App\Models\Message::where('sender_id', $id)->delete();
            \App\Models\Message::where('receiver_id', $id)->delete();
            // Tokens cascade via DB constraint usually, but we can force if needed
            // $worker->liveTokens()->delete(); 
        } else {
            // Keep data but make anonymous or orphan
            // Unlink tokens
            \App\Models\LiveToken::where('user_id', $id)->update(['user_id' => null]);
            // Messages remain as is (orphaned)
        }

        $worker->delete();
        return redirect()->back()->with('success', 'Worker deleted successfully!');
    }

    public function pending()
    {
        $pendingUsers = \App\Models\User::where('status', 'pending')->orderBy('created_at', 'desc')->get();
        return view('admin.workers.pending', compact('pendingUsers'));
    }

    public function approve($id)
    {
        $worker = \App\Models\User::findOrFail($id);
        $worker->update(['status' => 'active']);
        return redirect()->back()->with('success', 'User approved successfully!');
    }

    public function reject($id)
    {
        $worker = \App\Models\User::findOrFail($id);
        $worker->update(['status' => 'rejected']);
        return redirect()->back()->with('success', 'User rejected successfully!');
    }

    public function suspend($id)
    {
        $worker = \App\Models\User::findOrFail($id);
        $worker->update(['status' => 'suspended']);
        return redirect()->back()->with('success', 'User suspended successfully!');
    }

    public function activate($id)
    {
        $worker = \App\Models\User::findOrFail($id);
        $worker->update(['status' => 'active']);
        return redirect()->back()->with('success', 'User activated successfully!');
    }

    public function suspended()
    {
        $suspendedUsers = \App\Models\User::where('status', 'suspended')->orderBy('created_at', 'desc')->get();
        return view('admin.workers.suspended', compact('suspendedUsers'));
    }

    public function rejected()
    {
        $rejectedUsers = \App\Models\User::where('status', 'rejected')->orderBy('created_at', 'desc')->get();
        return view('admin.workers.rejected', compact('rejectedUsers'));
    }

    public function admins()
    {
        $admins = \App\Models\User::where('role', 'admin')->orderBy('created_at', 'desc')->get();
        return view('admin.workers.admins', compact('admins'));
    }
}
