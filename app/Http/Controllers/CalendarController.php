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

        return ResponseHelper::created($new, 'Event created successfully');
    }

    public function cancel_event($id)
    {
        try {
            $remove = Calendar::findOrFail($id)->delete();
            return ResponseHelper::success('Event deleted successfuly');
        } catch (\Exception $e) {
            return ResponseHelper::error('Event does not exist');
        }
    }

    public function all_events()
    {
        $all_events = Calendar::query()->get()->toArray();
        if (empty($all_events)) {
            return ResponseHelper::success('events not found');
        } else {
            return ResponseHelper::success($all_events, null, 'All Events :', 200);
        }
    }

    public function update_event(CalendarRequest $request, $id)
    {
        $validate = $request->validated();
        $updated_event = Calendar::findOrFail($id);
        $updated_event->update($validate);

        return ResponseHelper::updated($updated_event, 'Event updated successfully');
    }

    public function day_events()
    {
        $today = Calendar::whereDate('start', now()
            ->format('Y-m-d'))
            ->get()
            ->toArray();

        if (empty($today)) {
            return ResponseHelper::success('events not found');
        } else {
            return ResponseHelper::success($today, null, 'Today events returned successfully', 200);
        }
    }




    public function getEvenetsByDay(Request $request, $date)
    {
        $data = Calendar::whereDate('start', $date)->get()->toArray();

        if (empty($data)) {
            return ResponseHelper::success('events not found');
        } else {
            foreach ($data as &$event) {
                $start = Carbon::parse($event['start']);
                $event['day'] = $start->day;
            }

            return ResponseHelper::success($data, null, 'events by date', 200);
        }
    }

    public function week_events()
    {
        $after_week = now()->addDays(7);
        $this_week = Calendar::whereBetween('start', [now()->format('Y-m-d'), $after_week])
            ->get()->toArray();
        if (empty($this_week)) {
            return ResponseHelper::success('events not found');
        } else {
            return ResponseHelper::success($this_week, null, 'This week events returned successfully', 200);
        }
    }

    public function month_events()
    {
        $monthStart = now()->startOfMonth()->format('Y-m-d');
        $monthEnd = now()->endOfMonth()->format('Y-m-d');
        $this_month = Calendar::whereBetween('start', [$monthStart, $monthEnd])
            ->get()
            ->toArray();
        if (empty($this_month)) {
            return ResponseHelper::success('events not found');
        } else {
            return ResponseHelper::success($this_month, null, 'This month events returned successfully', 200);
        }
    }
}
