<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Calendar;
use App\Helper\ResponseHelper;
use App\Http\Requests\CalendarRequest;
use Carbon\Carbon;

class CalendarController extends Controller
{
    public function add_event(CalendarRequest $request)
    {
        $validate = $request->validated();
        $new = Calendar::create($validate);

        return ResponseHelper::created($new,'Event created successfully');
    }

    public function cancel_event($id)
    {
        $remove = Calendar::findOrFail($id)->delete();

        return ResponseHelper::deleted('Event canceled successfully');
    }

    public function all_events()
    {
        $all_events = Calendar::query()->get();

        return ResponseHelper::success($all_events, null, 'All Events :', 200);
    }

    public function update_event(CalendarRequest $request,$id)
    {
        $validate = $request->validated();
        $updated_event = Calendar::findOrFail($id);
        $updated_event->update($validate);

        return ResponseHelper::updated($updated_event,'Event updated successfully');
    }

    public function day_events()
    {
        $today = Calendar::whereDate('start_date',now()->format('Y-m-d'))->get();

        return ResponseHelper::success($today, null, 'Today events returned successfully', 200);
    }



    public function getEvenetsByDay(Request $request, $date)
    {
        $data = Calendar::whereDate('start_date', $date)->get()->toArray();
        if (empty($data)) {
            return ResponseHelper::success('events not found');

        } else {
            return ResponseHelper::success($data, null, 'events by date', 200);

        }
    }

    public function week_events()
    {
        $after_week = now()->addDays(7);
        $this_week = Calendar::whereBetween('start_date',[now()->format('Y-m-d'),$after_week])->get();

        return ResponseHelper::success($this_week, null, 'This week events returned successfully', 200);
    }

    public function month_events()
    {
        $monthstart = now()->startOfMonth()->format('Y-m-d');
        $monthend = now()->endOfMonth()->format('Y-m-d');
        $this_month = Calendar::whereBetween('start_date', [$monthstart, $monthend])->get();

        return ResponseHelper::success($this_month, null, 'This month events returned successfully', 200);
    }
}
