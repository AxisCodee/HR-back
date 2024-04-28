<?php

namespace App\Http\Controllers;

use App\Http\Requests\CalendarRequest\StoreCalendarRequest;
use App\Helper\ResponseHelper;
use App\Services\CalendarService;

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
    public function add_event(StoreCalendarRequest $request)
    {
        return $this->CalenderService->store($request);
    }

    /**
     * Delete an existing event by id.
     */
    public function cancel_event($id)
    {

            $removed = $this->CalenderService->destroy($id);
            return $removed;

    }

    /**
     * Get all existing events.
     */
    public function all_events()
    {
            $all = $this->CalenderService->All();
            return $all;

    }

    /**
     * Update an existing event by id.
     */
    public function update_event(StoreCalendarRequest $request, $id)
    {
            $update = $this->CalenderService->update($request, $id);
            return $update;

    }

    /**
     * Get events of the current day.
     */
    public function day_events()
    {
            $today = $this->CalenderService->today();
            return $today;

    }

    /**
     * Get all events of a specific date.
     */
    public function getEvenetsByDay($date)
    {
            $specific = $this->CalenderService->specific_date($date);
            return $specific;

    }

    /**
     * Get all events of this week.
     */
    public function week_events()
    {
            $week = $this->CalenderService->weekly();
            return $week;

    }

    /**
     * Get all events of this month.
     */
    public function month_events()
    {
            $month = $this->CalenderService->monthly();
            return $month;
      
    }
}
