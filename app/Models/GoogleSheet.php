<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class GoogleSheet extends Model
{
    protected $fillable = [
        'slug',
        'title',
        'url',
        'icon',
        'permission_type',
        'shift',
        'is_visible',
        'order',
    ];

    protected $casts = [
        'is_visible' => 'boolean',
    ];

    /**
     * Get all visible sheets ordered by 'order' column
     */
    public static function getVisibleSheets()
    {
        return static::where('is_visible', true)
            ->orderBy('order')
            ->orderBy('title')
            ->get();
    }

    /**
     * Get all sheets for admin management
     */
    public static function getAllSheets()
    {
        return static::orderBy('order')
            ->orderBy('title')
            ->get();
    }

    /**
     * Check if a user can access this sheet
     */
    public function canAccess($user): bool
    {
        // Admins can access everything
        if ($user->role === 'admin') {
            return true;
        }

        // Public sheets are accessible to everyone
        if ($this->permission_type === 'public') {
            return true;
        }

        // Admin-only sheets
        if ($this->permission_type === 'admin_only') {
            return false;
        }

        // Shift-based: user's shift must match
        if ($this->permission_type === 'shift_based') {
            return $user->shift === $this->shift;
        }

        return false;
    }

    /**
     * Generate a unique slug from title
     */
    public static function generateSlug(string $title, ?int $excludeId = null): string
    {
        $slug = Str::slug($title, '_');
        $originalSlug = $slug;
        $counter = 1;

        $query = static::where('slug', $slug);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        while ($query->exists()) {
            $slug = $originalSlug . '_' . $counter;
            $counter++;
            $query = static::where('slug', $slug);
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }
        }

        return $slug;
    }

    /**
     * Get available shifts for dropdown
     */
    public static function getAvailableShifts(): array
    {
        return [
            'Morning 8 Hours' => 'Morning 8 Hours',
            'Morning 8 Hours Female' => 'Morning 8 Hours Female',
            'Evening 8 Hours' => 'Evening 8 Hours',
            'Night 8 Hours' => 'Night 8 Hours',
            'Day 12 Hours' => 'Day 12 Hours',
            'Night 12 Hours' => 'Night 12 Hours',
        ];
    }
}
