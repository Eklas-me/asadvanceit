<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CleanupUnusedImages extends Command
{
    protected $signature = 'cleanup:images';
    protected $description = 'Delete unused images from uploads folder';

    public function handle()
    {
        $this->info('Starting image cleanup...');

        // 1. Get all used images from database
        $userPhotos = \App\Models\User::whereNotNull('profile_photo')->pluck('profile_photo')->toArray();

        $siteSettings = \App\Models\SiteSetting::whereIn('key', ['site_logo', 'site_favicon'])
            ->pluck('value')
            ->map(function ($path) {
                return basename($path);
            })
            ->toArray();

        // 2. Scan public/uploads (User Profile Photos)
        $uploadPath = public_path('uploads');
        $files = glob($uploadPath . '/*.*'); // Get all files
        $deletedCount = 0;

        foreach ($files as $file) {
            $filename = basename($file);

            // Skip directories or critical files if any (though glob *.* mostly gets files)
            if (is_dir($file))
                continue;

            if (!in_array($filename, $userPhotos)) {
                unlink($file);
                $this->line("Deleted: $filename");
                $deletedCount++;
            }
        }

        $this->info("Cleaned up $deletedCount unused profile photos.");

        // 3. Scan public/uploads/site (Site Assets)
        $sitePath = public_path('uploads/site');
        $siteFiles = glob($sitePath . '/*.*');
        $siteDeletedCount = 0;

        if (is_dir($sitePath)) {
            foreach ($siteFiles as $file) {
                $filename = basename($file);
                if (is_dir($file))
                    continue;

                if (!in_array($filename, $siteSettings)) {
                    unlink($file);
                    $this->line("Deleted site asset: $filename");
                    $siteDeletedCount++;
                }
            }
        }

        $this->info("Cleaned up $siteDeletedCount unused site assets.");
        $this->info('Cleanup complete!');
    }
}
