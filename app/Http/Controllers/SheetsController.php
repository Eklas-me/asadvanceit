<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SheetsController extends Controller
{
    // Configuration of Sheets
    private const SHEETS = [
        'facebook' => [
            'title' => 'Facebook',
            'url' => 'https://docs.google.com/spreadsheets/d/1enkFE-ngu2C7uUzY_YpXPXl7uLMgev6JDAnVgaTXL1k/edit?gid=0#gid=0',
            'public' => true
        ],
        'morning_8_hours' => [
            'title' => 'Morning 8 Hours',
            'url' => 'https://docs.google.com/spreadsheets/d/1-eqhWV3Ke9QbU2c_wTRxbKwW8m54uSnLvq1tD59IjeA/edit?usp=sharing',
            'shift' => 'Morning 8 Hours'
        ],
        'morning_8_hours_female' => [
            'title' => 'Morning 8 Hours Female',
            'url' => 'https://docs.google.com/spreadsheets/d/1dYyVPryN_OU64EWXXvXBCZvPQ2IGCDVtfG-ZPXngyto/edit?usp=sharing',
            'shift' => 'Morning 8 Hours Female'
        ],
        'evening_8_hours' => [
            'title' => 'Evening 8 Hours',
            'url' => 'https://docs.google.com/spreadsheets/d/14nnJMhx9E2ZyeGL8C0ppJqqsVxs2yGL2q9y3ZzWVXcE/edit?usp=sharing',
            'shift' => 'Evening 8 Hours'
        ],
        'night_8_hours' => [
            'title' => 'Night 8 Hours',
            'url' => 'https://docs.google.com/spreadsheets/d/19r6rX2nCQalnrR55qCVNAqOntyazAtMLJ0SEip9daac/edit?usp=sharing',
            'shift' => 'Night 8 Hours'
        ],
        'day_12_hours' => [
            'title' => 'Day 12 Hours',
            'url' => 'https://docs.google.com/spreadsheets/d/1ZpjtFd1T5kNEgEqdqYYiDX16uVni9q1Tqdd4ix501G0/edit?usp=sharing',
            'shift' => 'Day 12 Hours'
        ],
        'night_12_hours' => [
            'title' => 'Night 12 Hours',
            'url' => 'https://docs.google.com/spreadsheets/d/1wsPUJmukh8ih7vFP9nMH_vTMHeusEcqUnuseO4N7LRo/edit?usp=sharing',
            'shift' => 'Night 12 Hours'
        ]
    ];

    public static function getVisibleSheets()
    {
        $visibility = json_decode(getSetting('sheet_visibility', '{}'), true);
        $visibleSheets = [];

        foreach (self::SHEETS as $key => $config) {
            if (($visibility[$key] ?? 'on') === 'on') {
                $visibleSheets[$key] = $config;
            }
        }

        return $visibleSheets;
    }

    public function show($slug)
    {
        // 1. Check if sheet exists and is visible
        $visibleSheets = self::getVisibleSheets();

        if (!array_key_exists($slug, $visibleSheets)) {
            abort(404, 'Sheet not found or is currently disabled.');
        }

        $sheet = $visibleSheets[$slug];
        $user = Auth::user();

        // 2. Access Control
        $hasAccess = false;

        // Public sheets are accessible to everyone logged in
        if (!empty($sheet['public'])) {
            $hasAccess = true;
        }
        // Admins can access everything (already filtered by visibility above, but keeping for logic)
        elseif ($user->role === 'admin') {
            $hasAccess = true;
        }
        // Users can only access their assigned shift
        elseif (isset($sheet['shift']) && $user->shift === $sheet['shift']) {
            $hasAccess = true;
        }

        if (!$hasAccess) {
            abort(403, 'Unauthorized access to this sheet.');
        }

        // 3. Return View
        return view('sheets.show', [
            'title' => $sheet['title'],
            'url' => $sheet['url'],
            'slug' => $slug
        ]);
    }
}
