<?php

namespace App\Http\Controllers;

use App\Models\Request;
use App\Http\Requests\StoreRequestRequest;
use App\Http\Requests\UpdateRequestRequest;
use App\Helper\ResponseHelper;
use App\Http\Requests\SendRequest;
use App\Models\Absences;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class RequestController extends Controller
{

    public function index()
    {
        $results = Request::query()
            ->with('user:id,first_name,last_name')
            ->with('user.department:id,name')
            ->get()
            ->toArray();

        if (empty($results)) {
            return ResponseHelper::success('No requests available');
        }

        return ResponseHelper::success($results, null, 'All requests', 200);
    }


    public function store(StoreRequestRequest $request)
    {
        $requests = Request::query()
            ->create(
                [
                    'user_id' => Auth::id(),
                    'title' => $request->title,
                    'type' => $request->type,
                    'date' => Carbon::now(),
                    'description' => $request->description,
                    'status' => 'waiting'
                ]
            );
        return ResponseHelper::created($requests, 'request created successfully');
    }

    public function show(Request $request)
    {
        $result = Request::query()
            ->where('user_id', Auth::user()->id)
            ->get()
            ->toArray();
        return ResponseHelper::success($result, 'my requests:');
    }

    public function getRequest(Request $request)
    {
        $result = Request::query()
            ->with('user:id,first_name,last_name')
            ->with('user.department:id,name')
            ->get()
            ->toArray();

        if (empty($result)) {
            return ResponseHelper::success('No requests found for the user');
        }

        return ResponseHelper::success($result, 'My requests:');
    }

    public function update(UpdateRequestRequest $request, $id)
    {
        $request = Request::query()
            ->where('id', $id)
            ->where('status', 'waiting')
            ->update([
                'title' => $request->title,
                'type' => $request->type,
                'description' => $request->description
            ]);

        if ($request) {

            return ResponseHelper::updated('Request updated successfully');
        } else {
            return ResponseHelper::success('You cannot update this request');
        }
    }

    public function destroy(Request $request)
    {
        if ($request->status == 'waiting') {
            $request->delete();
        } else {
            return ResponseHelper::error('You cannot delete this request', null, 'error', 403);
        }
        return ResponseHelper::deleted('Request deleted successfully');
    }

    public function accepteRequest(Request $request)
    {
        $request->update(
            [
                'status' => 'accepted'
            ]
        );
        return ResponseHelper::updated([
            'message' => 'request accepted successfully',
        ]);
    }
    public function rejectRequest(Request $request)
    {
        $request->update(
            [
                'status' => 'rejected'
            ]
        );
        return ResponseHelper::success([
            'message' => 'request rejected successfully',
        ]);
    }
    public function addComplaint(Request $request)
    {
        $complaint = Request::query()->create(
            [
                'user_id' => Auth::id(),
                'type' => 'complaint',
                'description' => $request->description

            ]
        );
        return ResponseHelper::created($complaint, 'request created successfully');
    }

    public function getComplaints()
    {
        $result = Request::query()->with('users')
            ->where('type', 'complaint')
            ->get()->toArray();
        return ResponseHelper::success($result, 'your request');
    }

    public function send_request(SendRequest $request)
    {
        $validate = $request->validated();
        switch ($validate['duration']) {
            case 'hourly':
                $start_vac = Carbon::parse($validate['startDate']);
                $end_vac = Carbon::parse($validate['endDate']);
                $hours_number = $start_vac->diffInHours($end_vac);
                $new_req = Absences::create([
                    'user_id' => $validate['user_id'],
                    'startDate' => $start_vac,
                    'endDate' => $end_vac,
                    'duration' => $validate['duration'],
                    'hours_num' => $hours_number,
                ]);
                return ResponseHelper::created($new_req, 'Request sent successfully');
                break;
            case 'daily':
                $new_req = Absences::create([
                    'user_id' => $validate['user_id'],
                    'startDate' => $validate['startDate'],
                    'endDate' => $validate['endDate'],
                    'duration' => $validate['duration'],
                ]);
                return ResponseHelper::created($new_req, 'Request sent successfully');
                break;
            default:
                return ResponseHelper::error($validate, null, 'error sending the request', 400);
                break;
        }
    }
}
