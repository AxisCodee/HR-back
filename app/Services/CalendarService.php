<?php

namespace App\Services;


use Illuminate\Http\Request;
use App\Models\Calendar;
use App\Helper\ResponseHelper;
use App\Http\Requests\CalendarRequest;
use Carbon\Carbon;

class CalendarService
{
    public function All()
    {
        $all_events = Calendar::query()->where('branch_id', request('branch_id'))->get()->toArray();
        if (empty($all_events)) {
            return ResponseHelper::success('events not found');
        } else {
            return ResponseHelper::success($all_events, null, 'All Events :', 200);
        }
    }

    public function store($request)
    {
        $validate = $request->validated();
        $validate['branch_id'] = $request->branch_id;
        $new = Calendar::create($validate);
        return ResponseHelper::success($new,'Event created successfuly');
    }

    public function destroy($id)
    {
        Calendar::findOrFail($id)->delete();
        return ResponseHelper::success('Event deleted successfuly');
    }

    public function update($request, $id)
    {
        $validate = $request->validated();
        $updated_event = Calendar::findOrFail($id);
        $updated_event->update($validate);
        return ResponseHelper::updated($updated_event, 'Event updated successfully');
    }

    public function today()
    {
        $today = Calendar::whereDate('start', now()
            ->where('branch_id', \request('branch_id'))
            ->format('Y-m-d'))
            ->get()
            ->toArray();
        if (empty($today)) {
            return ResponseHelper::success('events not found');
        } else {
            return ResponseHelper::success($today, null, 'Today events returned successfully', 200);
        }
    }

    public function specific_date($date)
    {
        $data = Calendar::whereDate('start', $date)->where('branch_id', \request('branch_id'))->get()->toArray();
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

    public function weekly()
    {
        $after_week = now()->addDays(7);
        $this_week = Calendar::whereBetween('start', [now()->format('Y-m-d'), $after_week])
            ->where('branch_id', \request('branch_id'))
            ->get()->toArray();
        if (empty($this_week)) {
            return ResponseHelper::success('events not found');
        } else {
            return ResponseHelper::success($this_week, null, 'This week events returned successfully', 200);
        }
    }

    public function monthly()
    {
        $monthStart = now()->startOfMonth()->format('Y-m-d');
        $monthEnd = now()->endOfMonth()->format('Y-m-d');
        $this_month = Calendar::whereBetween('start', [$monthStart, $monthEnd])
            ->where('branch_id', \request('branch_id'))
            ->get()
            ->toArray();
        if (empty($this_month)) {
            return ResponseHelper::success('events not found');
        } else {
            return ResponseHelper::success($this_month, null, 'This month events returned successfully', 200);
        }
    }
}
