<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    public function index()
    {
        return view('admin.settings.index');
    }

    public function clearCache()
    {
        // Clear all caches
        Artisan::call('optimize:clear');

        return back()->with('success', 'System cache cleared successfully!');
    }

    public function updateLogo(Request $request)
    {
        $request->validate([
            'logo' => 'required|image|mimes:png,jpg,jpeg,svg|max:4096', // Max 4MB
        ]);

        if ($request->hasFile('logo')) {
            try {
                if (!$request->file('logo')->isValid()) {
                    return back()->with('error', 'Uploaded logo is not valid.');
                }

                // Store file public path relative
                $path = $request->file('logo')->store('site-settings', 'public');

                // Save 'storage/...' so asset() helper works directly
                setSetting('site_logo', 'storage/' . $path);

                return back()->with('success', 'Logo updated successfully!');
            } catch (\Exception $e) {
                \Log::error('Logo upload failed: ' . $e->getMessage());
                return back()->with('error', 'Upload failed: ' . $e->getMessage());
            }
        }

        return back()->with('error', 'Please upload a valid logo file.');
    }

    public function updateFavicon(Request $request)
    {
        $request->validate([
            'favicon' => 'required|mimes:ico,png|max:1024', // Max 1MB
        ]);

        if ($request->hasFile('favicon')) {
            try {
                if (!$request->file('favicon')->isValid()) {
                    return back()->with('error', 'Uploaded favicon is not valid.');
                }

                $path = $request->file('favicon')->store('site-settings', 'public');
                setSetting('site_favicon', 'storage/' . $path);

                return back()->with('success', 'Favicon updated successfully!');
            } catch (\Exception $e) {
                \Log::error('Favicon upload failed: ' . $e->getMessage());
                return back()->with('error', 'Upload failed: ' . $e->getMessage());
            }
        }

        return back()->with('error', 'Please upload a valid favicon file.');
    }
    public function removeLogo()
    {
        $currentLogo = getSetting('site_logo');
        if ($currentLogo) {
            // Check if it's a storage path
            if (str_starts_with($currentLogo, 'storage/')) {
                $relativePath = str_replace('storage/', '', $currentLogo);
                Storage::disk('public')->delete($relativePath);
            } elseif (file_exists(public_path($currentLogo))) {
                @unlink(public_path($currentLogo));
            }
        }

        \App\Models\SiteSetting::where('key', 'site_logo')->delete();

        return back()->with('success', 'Logo removed successfully. Reverted to default.');
    }

    public function removeFavicon()
    {
        $currentFavicon = getSetting('site_favicon');
        if ($currentFavicon) {
            // Check if it's a storage path
            if (str_starts_with($currentFavicon, 'storage/')) {
                $relativePath = str_replace('storage/', '', $currentFavicon);
                Storage::disk('public')->delete($relativePath);
            } elseif (file_exists(public_path($currentFavicon))) {
                @unlink(public_path($currentFavicon));
            }
        }

        \App\Models\SiteSetting::where('key', 'site_favicon')->delete();

        return back()->with('success', 'Favicon removed successfully. Reverted to default.');
    }

    public function updateTelegram(Request $request)
    {
        $request->validate([
            'telegram_bot_token' => 'required|string',
            'telegram_admin_chat_id' => 'required|string',
        ]);

        try {
            \App\Models\SiteSetting::set('telegram_bot_token', $request->telegram_bot_token);
            \App\Models\SiteSetting::set('telegram_admin_chat_id', $request->telegram_admin_chat_id);

            return back()->with('success', 'Telegram settings updated successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update settings: ' . $e->getMessage());
        }
    }
    public function updateSheetVisibility(Request $request)
    {
        // This method is now deprecated but kept for backwards compatibility
        // New dynamic system uses GoogleSheet model directly
        return back()->with('success', 'Sheet visibility updated successfully!');
    }

    /**
     * Store a new Google Sheet
     */
    public function storeSheet(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'url' => 'required|url',
            'icon' => 'nullable|string|max:100',
            'permission_type' => 'required|in:public,shift_based,admin_only',
            'shift' => 'required_if:permission_type,shift_based|nullable|string',
        ]);

        try {
            $slug = \App\Models\GoogleSheet::generateSlug($request->title);

            \App\Models\GoogleSheet::create([
                'slug' => $slug,
                'title' => $request->title,
                'url' => $request->url,
                'icon' => $request->icon ?: 'fas fa-file-excel',
                'permission_type' => $request->permission_type,
                'shift' => $request->permission_type === 'shift_based' ? $request->shift : null,
                'is_visible' => true,
                'order' => \App\Models\GoogleSheet::max('order') + 1,
            ]);

            return back()->with('success', 'Google Sheet added successfully!');
        } catch (\Exception $e) {
            \Log::error('Failed to add sheet: ' . $e->getMessage());
            return back()->with('error', 'Failed to add sheet: ' . $e->getMessage());
        }
    }

    /**
     * Update an existing Google Sheet
     */
    public function updateSheet(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'url' => 'required|url',
            'icon' => 'nullable|string|max:100',
            'permission_type' => 'required|in:public,shift_based,admin_only',
            'shift' => 'required_if:permission_type,shift_based|nullable|string',
        ]);

        try {
            $sheet = \App\Models\GoogleSheet::findOrFail($id);

            $sheet->update([
                'title' => $request->title,
                'url' => $request->url,
                'icon' => $request->icon ?: 'fas fa-file-excel',
                'permission_type' => $request->permission_type,
                'shift' => $request->permission_type === 'shift_based' ? $request->shift : null,
            ]);

            return back()->with('success', 'Google Sheet updated successfully!');
        } catch (\Exception $e) {
            \Log::error('Failed to update sheet: ' . $e->getMessage());
            return back()->with('error', 'Failed to update sheet: ' . $e->getMessage());
        }
    }

    /**
     * Delete a Google Sheet
     */
    public function deleteSheet($id)
    {
        try {
            $sheet = \App\Models\GoogleSheet::findOrFail($id);
            $sheet->delete();

            return back()->with('success', 'Google Sheet deleted successfully!');
        } catch (\Exception $e) {
            \Log::error('Failed to delete sheet: ' . $e->getMessage());
            return back()->with('error', 'Failed to delete sheet: ' . $e->getMessage());
        }
    }

    /**
     * Toggle visibility of a Google Sheet
     */
    public function toggleSheetVisibility($id)
    {
        try {
            $sheet = \App\Models\GoogleSheet::findOrFail($id);
            $sheet->update(['is_visible' => !$sheet->is_visible]);

            $status = $sheet->is_visible ? 'visible' : 'hidden';
            return back()->with('success', "Sheet is now {$status}!");
        } catch (\Exception $e) {
            \Log::error('Failed to toggle sheet: ' . $e->getMessage());
            return back()->with('error', 'Failed to toggle sheet visibility.');
        }
    }

    /**
     * Update Agent App Settings (Version, Download URL, Signature)
     */
    public function updateAgentApp(Request $request)
    {
        // Increase memory and execution time for large uploads
        ini_set('memory_limit', '256M');
        set_time_limit(300);

        // Custom validation messages to help diagnose server-side issues
        $messages = [
            'agent_update_file.max' => 'The file size exceeds the allowed limit (100MB).',
            'agent_update_file.mimes' => 'The file must be a zip, msi, or exe file.',
            'agent_download_url.required_without' => 'Please provide either an update file or a manual download URL.',
        ];

        try {
            $request->validate([
                'agent_version' => 'required|string|max:50',
                'agent_update_file' => 'nullable|file|mimes:zip,msi,exe,bin,octet-stream|max:102400', // Increased to 100MB
                'agent_download_url' => 'nullable', // Removed url validation if empty or file presented
                'agent_signature' => 'required|string',
                'agent_notes' => 'nullable|string',
            ], $messages);

            // Custom check: either file or URL must be present
            if (!$request->hasFile('agent_update_file') && empty($request->agent_download_url)) {
                return back()->withInput()->withErrors(['agent_download_url' => 'Please provide either an update file or a manual download URL.']);
            }
            // Check if URL is valid if provided
            if (empty($request->file('agent_update_file')) && !empty($request->agent_download_url) && !filter_var($request->agent_download_url, FILTER_VALIDATE_URL)) {
                return back()->withInput()->withErrors(['agent_download_url' => 'The agent download url must be a valid URL.']);
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Check if the file was missing despite being sent (indicates post_max_size or upload_max_filesize limit hit)
            if ($request->has('agent_update_file') && !$request->hasFile('agent_update_file')) {
                $max_post = ini_get('post_max_size');
                $max_upload = ini_get('upload_max_filesize');
                return back()->withInput()->with('error', "Upload failed! The file might be larger than your server supports (Current server limits: upload_max_filesize={$max_upload}, post_max_size={$max_post}). Please increase these in your live server settings.");
            }
            throw $e;
        }

        try {
            if ($request->hasFile('agent_update_file')) {
                $file = $request->file('agent_update_file');
                $extension = $file->getClientOriginalExtension();

                // Fallback for files without extension or octet-stream
                if (!$extension) {
                    $extension = 'zip';
                }

                $filename = $file->getClientOriginalName();

                // Store in public/agent-updates
                $path = $file->storeAs('agent-updates', $filename, 'public');

                // Generate URL
                $downloadUrl = asset('storage/' . $path);
                \App\Models\SiteSetting::set('agent_download_url', $downloadUrl);
            } elseif ($request->agent_download_url) {
                \App\Models\SiteSetting::set('agent_download_url', $request->agent_download_url);
            }

            \App\Models\SiteSetting::set('agent_version', $request->agent_version);
            \App\Models\SiteSetting::set('agent_signature', $request->agent_signature);
            \App\Models\SiteSetting::set('agent_notes', $request->agent_notes);

            return redirect()->route('admin.settings.index', ['tab' => 'agent-app'])->with('success', 'Agent App settings updated successfully!');
        } catch (\Exception $e) {
            \Log::error('Agent App Update Error: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Failed to update settings: ' . $e->getMessage());
        }
    }
}
