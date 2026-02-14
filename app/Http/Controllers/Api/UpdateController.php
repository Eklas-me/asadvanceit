<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UpdateController extends Controller
{
    /**
     * Check for updates for the Agent App.
     * 
     * Target: windows-x86_64, etc.
     * Arch: x86_64, etc.
     * Current Version: 1.0.0
     * 
     * Response format: https://tauri.app/v1/guides/features/updater
     */
    public function check(Request $request, $target, $current_version)
    {
        $latestVersion = \App\Models\SiteSetting::get('agent_version', '1.0.0');

        if (version_compare($latestVersion, $current_version, '>')) {
            return response()->json([
                'version' => 'v' . $latestVersion,
                'notes' => \App\Models\SiteSetting::get('agent_notes', 'New update available.'),
                'pub_date' => now()->toIso8601String(),
                'platforms' => [
                    $target => [
                        'signature' => \App\Models\SiteSetting::get('agent_signature', ''),
                        'url' => \App\Models\SiteSetting::get('agent_download_url'),
                    ]
                ]
            ]);
        }

        return response()->json([], 204); // No update available
    }
}
