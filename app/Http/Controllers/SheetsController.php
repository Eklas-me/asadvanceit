<?php

namespace App\Http\Controllers;

use App\Models\GoogleSheet;
use Illuminate\Support\Facades\Auth;

class SheetsController extends Controller
{
    /**
     * Get all visible sheets that the current user can access (for sidebar)
     */
    public static function getVisibleSheets()
    {
        $user = Auth::user();
        $sheets = GoogleSheet::getVisibleSheets();

        return $sheets->filter(function ($sheet) use ($user) {
            return $sheet->canAccess($user);
        });
    }

    /**
     * Show a specific sheet
     */
    public function show($slug)
    {
        $sheet = GoogleSheet::where('slug', $slug)->first();

        if (!$sheet) {
            abort(404, 'Sheet not found.');
        }

        if (!$sheet->is_visible) {
            abort(404, 'This sheet is currently disabled.');
        }

        $user = Auth::user();

        if (!$sheet->canAccess($user)) {
            abort(403, 'Unauthorized access to this sheet.');
        }

        return view('sheets.show', [
            'title' => $sheet->title,
            'url' => $sheet->url,
            'slug' => $sheet->slug
        ]);
    }
}

