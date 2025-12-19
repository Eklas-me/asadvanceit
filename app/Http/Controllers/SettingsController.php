<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

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
                $file = $request->file('logo');
                $filename = 'logo_' . time() . '.' . $file->getClientOriginalExtension();
                $path = public_path('uploads/site');

                if (!file_exists($path)) {
                    mkdir($path, 0755, true);
                }

                $file->move($path, $filename);

                setSetting('site_logo', 'uploads/site/' . $filename);

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
                $file = $request->file('favicon');
                $filename = 'favicon_' . time() . '.' . $file->getClientOriginalExtension();
                $path = public_path('uploads/site');

                if (!file_exists($path)) {
                    mkdir($path, 0755, true);
                }

                $file->move($path, $filename);

                setSetting('site_favicon', 'uploads/site/' . $filename);

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
        if ($currentLogo && file_exists(public_path($currentLogo))) {
            @unlink(public_path($currentLogo));
        }

        \App\Models\SiteSetting::where('key', 'site_logo')->delete();

        return back()->with('success', 'Logo removed successfully. Reverted to default.');
    }

    public function removeFavicon()
    {
        $currentFavicon = getSetting('site_favicon');
        if ($currentFavicon && file_exists(public_path($currentFavicon))) {
            @unlink(public_path($currentFavicon));
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
        $inputSheets = $request->input('sheets', []);

        // Define all known sheets to ensure we handle 'off' states (unchecked checkboxes)
        $knownSheets = [
            'facebook',
            'morning_8_hours',
            'morning_8_hours_female',
            'evening_8_hours',
            'night_8_hours',
            'day_12_hours',
            'night_12_hours',
        ];

        $visibilities = [];
        foreach ($knownSheets as $key) {
            $visibilities[$key] = isset($inputSheets[$key]) ? 'on' : 'off';
        }

        try {
            // Store as a JSON string in site_settings
            \App\Models\SiteSetting::set('sheet_visibility', json_encode($visibilities));

            return back()->with('success', 'Sheet visibility updated successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update settings: ' . $e->getMessage());
        }
    }
}
