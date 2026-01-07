<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\GoogleSheet;

class GoogleSheetSeeder extends Seeder
{
    /**
     * Seed the existing hardcoded sheets into the database.
     */
    public function run(): void
    {
        $sheets = [
            [
                'slug' => 'facebook',
                'title' => 'Facebook',
                'url' => 'https://docs.google.com/spreadsheets/d/1enkFE-ngu2C7uUzY_YpXPXl7uLMgev6JDAnVgaTXL1k/edit?gid=0#gid=0',
                'icon' => 'fab fa-facebook',
                'permission_type' => 'public',
                'shift' => null,
                'is_visible' => true,
                'order' => 1,
            ],
            [
                'slug' => 'morning_8_hours',
                'title' => 'Morning 8 Hours',
                'url' => 'https://docs.google.com/spreadsheets/d/1-eqhWV3Ke9QbU2c_wTRxbKwW8m54uSnLvq1tD59IjeA/edit?usp=sharing',
                'icon' => 'fas fa-sun',
                'permission_type' => 'shift_based',
                'shift' => 'Morning 8 Hours',
                'is_visible' => true,
                'order' => 2,
            ],
            [
                'slug' => 'morning_8_hours_female',
                'title' => 'Morning 8 Hours Female',
                'url' => 'https://docs.google.com/spreadsheets/d/1dYyVPryN_OU64EWXXvXBCZvPQ2IGCDVtfG-ZPXngyto/edit?usp=sharing',
                'icon' => 'fas fa-female',
                'permission_type' => 'shift_based',
                'shift' => 'Morning 8 Hours Female',
                'is_visible' => true,
                'order' => 3,
            ],
            [
                'slug' => 'evening_8_hours',
                'title' => 'Evening 8 Hours',
                'url' => 'https://docs.google.com/spreadsheets/d/14nnJMhx9E2ZyeGL8C0ppJqqsVxs2yGL2q9y3ZzWVXcE/edit?usp=sharing',
                'icon' => 'fas fa-cloud-sun',
                'permission_type' => 'shift_based',
                'shift' => 'Evening 8 Hours',
                'is_visible' => true,
                'order' => 4,
            ],
            [
                'slug' => 'night_8_hours',
                'title' => 'Night 8 Hours',
                'url' => 'https://docs.google.com/spreadsheets/d/19r6rX2nCQalnrR55qCVNAqOntyazAtMLJ0SEip9daac/edit?usp=sharing',
                'icon' => 'fas fa-moon',
                'permission_type' => 'shift_based',
                'shift' => 'Night 8 Hours',
                'is_visible' => true,
                'order' => 5,
            ],
            [
                'slug' => 'day_12_hours',
                'title' => 'Day 12 Hours',
                'url' => 'https://docs.google.com/spreadsheets/d/1ZpjtFd1T5kNEgEqdqYYiDX16uVni9q1Tqdd4ix501G0/edit?usp=sharing',
                'icon' => 'fas fa-sun',
                'permission_type' => 'shift_based',
                'shift' => 'Day 12 Hours',
                'is_visible' => true,
                'order' => 6,
            ],
            [
                'slug' => 'night_12_hours',
                'title' => 'Night 12 Hours',
                'url' => 'https://docs.google.com/spreadsheets/d/1wsPUJmukh8ih7vFP9nMH_vTMHeusEcqUnuseO4N7LRo/edit?usp=sharing',
                'icon' => 'fas fa-moon',
                'permission_type' => 'shift_based',
                'shift' => 'Night 12 Hours',
                'is_visible' => true,
                'order' => 7,
            ],
        ];

        foreach ($sheets as $sheet) {
            GoogleSheet::updateOrCreate(
                ['slug' => $sheet['slug']],
                $sheet
            );
        }

        $this->command->info('Google Sheets seeded successfully!');
    }
}
