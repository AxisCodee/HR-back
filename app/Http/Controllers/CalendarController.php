<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Calendar;
use App\Helper\ResponseHelper;
use App\Http\Requests\CalendarRequest;
use App\Services\CalendarService;
use Carbon\Carbon;

use function PHPUnit\Framework\returnSelf;

class CalendarController extends Controller
{

    protected $CalenderService;

    /**
     * Define the constructor to use the Calendar services.
     */
    public function __construct(CalendarService $CalenderService)
    {
        $this->CalenderService = $CalenderService;
    }

    /**
     * Create a new event.
     */
    public function add_event(CalendarRequest $request)
    {
        try {
            $new = $this->CalenderService->store($request);
            return $new;
        } catch (\Exception $e) {
            return ResponseHelper::error($e);
        }
    }

    /**
     * Delete an existing event by id.
     */
    public function cancel_event($id)
    {
        try {
            $removed = $this->CalenderService->destroy($id);
            return $removed;
        } catch (\Exception $e) {
            return ResponseHelper::error($e);
        }
    }

    /**
     * Get all existing events.
     */
    public function all_events()
    {
        try {
            $all = $this->CalenderService->All();
            return $all;
        } catch (\Throwable $th) {
            return ResponseHelper::error($th);
        }
    }

    /**
     * Update an existing event by id.
     */
    public function update_event(CalendarRequest $request, $id)
    {
        try {
            $update = $this->CalenderService->update($request, $id);
            return $update;
        } catch (\Throwable $th) {
            return ResponseHelper::error($th);
        }
    }

    /**
     * Get events of the current day.
     */
    public function day_events()
    {
        try {
            $today = $this->CalenderService->today();
            return $today;
        } catch (\Throwable $th) {
            return ResponseHelper::error($th);
        }
    }

    /**
     * Get all events of a specific date.
     */
    public function getEvenetsByDay($date)
    {
        try {
            $specific = $this->CalenderService->specific_date($date);
            return $specific;
        } catch (\Throwable $th) {
            return ResponseHelper::error($th);
        }
    }

    /**
     * Get all events of this week.
     */
    public function week_events()
    {
        try {
            $week = $this->CalenderService->weekly();
            return $week;
        } catch (\Throwable $th) {
            return ResponseHelper::error($th);
        }
    }

    /**
     * Get all events of this month.
     */
    public function month_events()
    {
        try {
            $month = $this->CalenderService->monthly();
            return $month;
        } catch (\Throwable $th) {
            return ResponseHelper::error($th);
        }
    }
}
