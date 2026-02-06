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
        $filePath = public_path('apps/Advanced_IT_Setup.msi');

        if (!file_exists($filePath)) {
            return back()->withErrors(['error' => 'The application setup file is currently unavailable. Please contact the administrator.']);
        }

        return response()->download($filePath, 'Advanced_IT_Setup.msi');
    }
}
