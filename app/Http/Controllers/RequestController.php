<?php

namespace App\Http\Controllers;

use App\Models\Request;
use App\Http\Requests\StoreRequestRequest;
use App\Http\Requests\UpdateRequestRequest;
use App\Helper\ResponseHelper;
use App\Http\Requests\SendRequest;
use App\Models\Absences;
use App\Models\Decision;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RequestController extends Controller
{

    public function index(Request $request)
    {

        $branchId = $request->input('branch_id');
        $results = Request::query()
             ->with('user')->whereHas('user', function ($query) use ($branchId) {
                $query->where('branch_id', $branchId);
            })
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

    public function acceptRequest(Request $request)
{
    return DB::transaction(function() use($request){
        $request->update([
            'status' => 'accepted'
        ]);
    // تخزين السلفة بالقرارات ضفت نوع ادفانس بالقرارت كمان
    //  الكمية هي الراتب تقسيم 2 والنوع سلفة بس تتتتخزم هيك هي منحتاجا وقت نعرض الراتب المخصوم منو بالمودل
        if ($request->type == 'advanced') {
            $user = User::find($request->user_id);
            $salary = $user->salary;
            $result = Decision::query()->create([
                'user_id' => $request->user_id,
                'type' => 'advanced',
                'amount' => ($salary / 2) ,
                'dateTime' => $request->dateTime,
                'salary' => $salary
            ]);
        }

        return ResponseHelper::updated([
            'message' => 'Request accepted successfully',
        ]);
    });

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

    public function getComplaints($branchId)
    {
        $result = Request::query()->with('user')->whereHas('user', function ($query) use ($branchId) {
            $query->where('branch_id', $branchId);
        })
            ->where('type', 'complaint')
            ->get()->toArray();
        return ResponseHelper::success($result, 'your request');
    }

    public function send_request(SendRequest $request)
    {
        $validate = $request->validated();

        if($validate['duration'] == 'hourly')
        {
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
        }
            elseif ($validate['duration'] == 'daily')
            {
                $new_req = Absences::create([
                    'user_id' => $validate['user_id'],
                    'startDate' => $validate['startDate'],
                    'endDate' => $validate['endDate'],
                    'duration' => $validate['duration'],
                ]);
                return ResponseHelper::created($new_req, 'Request sent successfully');
            }
                else
                {
                    return ResponseHelper::error($validate, null, 'error sending the request', 400);
                }
    }
}
