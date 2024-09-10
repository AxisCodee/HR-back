<?php

namespace App\Models;

use App\Helper\CarbonDaysHelper;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Policy extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_time', 'annual_salary_increase',
        'warnings', 'absence_management', 'deduction_status', 'branch_id', 'demands_compensation',

        'monthlyhours',
    ];

    protected $casts = [
        'work_time' => 'array',
        'annual_salary_increase' => 'array',
        'warnings' => 'array',
        'absence_management' => 'array',
    ];

    public function getTotalWorkingHours($date)
    {
        $weekendDays = CarbonDaysHelper::getWeekEndDaysCarbon($this->work_time['work_days']);
        Carbon::setWeekendDays($weekendDays);
        CarbonPeriod::macro('countWeekdays', function () {
            return $this->filter('isWeekday')->count();
        });
        $month = Carbon::parse($date);
        $totalWorkingDaysInMonth = CarbonPeriod::create($month->copy()->startOfMonth(), $month->copy()->endOfMonth())->countWeekdays();
        $totalWorkingHoursInDay = Carbon::parse($this->work_time['start_time'])->diffInMinutes($this->work_time['end_time']) / 60;
        return $totalWorkingDaysInMonth * $totalWorkingHoursInDay;
    }
}
