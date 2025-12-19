<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\LiveToken;
use App\Models\User;

class ShiftService
{
    /**
     * Compute specific shift times based on shift name and reference date.
     */
    public function computeShiftTimes($shiftName, $refDate)
    {
        $refDate = $refDate instanceof Carbon ? $refDate->format('Y-m-d') : $refDate;

        switch (strtolower(trim($shiftName))) {
            case 'morning 8 hours female':
            case 'morning 8 hours':
                $start = Carbon::parse($refDate . ' 07:00:00');
                $end = Carbon::parse($refDate . ' 15:00:00');
                break;
            case 'day 12 hours':
                $start = Carbon::parse($refDate . ' 07:00:00');
                $end = Carbon::parse($refDate . ' 19:00:00');
                break;
            case 'evening 8 hours':
                $start = Carbon::parse($refDate . ' 15:00:00');
                $end = Carbon::parse($refDate . ' 23:00:00');
                break;
            case 'night 12 hours':
                $start = Carbon::parse($refDate . ' 19:00:00');
                $end = Carbon::parse($refDate . ' 07:00:00')->addDay();
                break;
            case 'night 8 hours':
                $start = Carbon::parse($refDate . ' 23:00:00');
                $end = Carbon::parse($refDate . ' 07:00:00')->addDay();
                break;
            default:
                $start = Carbon::parse($refDate . ' 07:00:00');
                $end = Carbon::parse($refDate . ' 15:00:00');
                break;
        }
        return ['start' => $start, 'end' => $end];
    }

    public function getAllShiftDefinitions($refDate)
    {
        $refDate = $refDate instanceof Carbon ? $refDate->format('Y-m-d') : $refDate;

        return [
            'morning_8' => ['name' => 'Morning 8 Hours', 'start' => Carbon::parse("$refDate 07:00:00"), 'end' => Carbon::parse("$refDate 15:00:00")],
            'day_12' => ['name' => 'Day 12 Hours', 'start' => Carbon::parse("$refDate 07:00:00"), 'end' => Carbon::parse("$refDate 19:00:00")],
            'evening_8' => ['name' => 'Evening 8 Hours', 'start' => Carbon::parse("$refDate 15:00:00"), 'end' => Carbon::parse("$refDate 23:00:00")],
            'night_12' => ['name' => 'Night 12 Hours', 'start' => Carbon::parse("$refDate 19:00:00"), 'end' => Carbon::parse("$refDate 07:00:00")->addDay()],
            'night_8' => ['name' => 'Night 8 Hours', 'start' => Carbon::parse("$refDate 23:00:00"), 'end' => Carbon::parse("$refDate 07:00:00")->addDay()]
        ];
    }

    public function findNearestShift($now)
    {
        $now = $now instanceof Carbon ? $now : Carbon::parse($now);
        $today = $now->format('Y-m-d');
        $shifts = $this->getAllShiftDefinitions($today);

        $nearest = null;
        $minDiff = PHP_INT_MAX;

        foreach ($shifts as $key => $shift) {
            $start = $shift['start'];
            $end = $shift['end'];

            // Check if within shift window with 2 hours buffer
            if ($now->between($start->copy()->subHours(2), $end->copy()->addHours(2))) {
                $diff = $now->diffInSeconds($start);
                if ($diff < $minDiff) {
                    $minDiff = $diff;
                    $nearest = $shift;
                }
            }
        }

        return $nearest ?? $shifts['morning_8']; // fallback
    }

    /**
     * Get the current shift boundaries (07:00 AM to 07:00 AM next day).
     * @return array [start, end]
     */
    public function getShiftBoundaries()
    {
        $now = Carbon::now();

        // Shift starts at 7 AM
        $shiftStart = Carbon::create($now->year, $now->month, $now->day, 7, 0, 0);

        // If current time is before 7 AM, the shift started yesterday at 7 AM
        if ($now->lt($shiftStart)) {
            $shiftStart->subDay();
        }

        $shiftEnd = $shiftStart->copy()->addDay();

        return [$shiftStart, $shiftEnd];
    }

    public function countTokensToday()
    {
        list($start, $end) = $this->getShiftBoundaries();
        return LiveToken::whereBetween('insert_time', [$start, $end])->count();
    }

    public function countWorkersToday()
    {
        list($start, $end) = $this->getShiftBoundaries();
        return LiveToken::whereBetween('insert_time', [$start, $end])->distinct('user_id')->count('user_id');
    }

    public function countAccountsToday()
    {
        return $this->countTokensToday();
    }

    public function countMyTokensToday($userId)
    {
        list($start, $end) = $this->getShiftBoundaries();
        return LiveToken::where('user_id', $userId)->whereBetween('insert_time', [$start, $end])->count();
    }

    public function countMyTokensYesterday($userId)
    {
        list($start, $end) = $this->getShiftBoundaries();
        $start->subDay();
        $end->subDay();
        return LiveToken::where('user_id', $userId)->whereBetween('insert_time', [$start, $end])->count();
    }

    public function countMyTokensMonth($userId)
    {
        $start = Carbon::now()->startOfMonth()->addHours(7);
        $end = $start->copy()->addMonth();
        return LiveToken::where('user_id', $userId)->whereBetween('insert_time', [$start, $end])->count();
    }

    public function countMyTokensLifetime($userId)
    {
        return LiveToken::where('user_id', $userId)->count();
    }

    /**
     * Get daily token counts for a specific user over the last N days.
     *
     * @param int $userId
     * @param int $days
     * @return array
     */
    public function getDailyTokenCounts($userId, $days = 7)
    {
        $dates = [];
        $counts = [];

        // Generate last N days
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            // Shift starts at 7 AM
            $start = Carbon::create($date->year, $date->month, $date->day, 7, 0, 0);
            $end = $start->copy()->addDay();

            $count = LiveToken::where('user_id', $userId)
                ->whereBetween('insert_time', [$start, $end])
                ->count();

            $dates[] = $date->format('M d'); // e.g., "Oct 25"
            $counts[] = $count;
        }

        return [
            'labels' => $dates,
            'data' => $counts
        ];
    }
}
