<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AppController extends Controller
{
    /**
     * Download the Agent Application
     */
    public function downloadAgent()
    {
        // Get dynamic download URL from settings
        $dynamicUrl = \App\Models\SiteSetting::get('agent_download_url');

        if ($dynamicUrl) {
            return redirect($dynamicUrl);
        }

        // Fallback to legacy path if no dynamic URL is set
        $filePath = public_path('apps/Advanced_IT_Setup.msi');

        if (!file_exists($filePath)) {
            return back()->withErrors(['error' => 'The application setup file is currently unavailable. Please contact the administrator.']);
        }

        return response()->download($filePath, 'Advanced_IT_Setup.msi');
    }
}
