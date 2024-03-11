<?php

namespace App\Services;

use App\Helper\ResponseHelper;
use App\Http\Requests\RequestRequest\StoreRequestRequest;
use App\Models\Absences;
use App\Models\Decision;
use App\Models\Request;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request as HttpRequest;

class RequestService
{
    public function index()
    {
        $branchId = HttpRequest::input('branch_id');
        $date = HttpRequest::input('date');
        $query = Request::query()
            ->with('user', function ($query) use ($branchId) {
                $query->where('branch_id', $branchId);
            })
            ->with('user.department:id,name', 'user.userInfo');
        if (!empty($date)) {
            $query->where(function ($query) use ($date) {
                if (strlen($date) == 4) {
                    $query->where('date', 'like', $date . '%');
                }
                if (strlen($date) == 7) {
                    $query->orWhere('date', 'like', substr($date, 0, 7) . '%');
                }
                if (strlen($date) == 10) {
                    $query->orWhere('date', 'like', substr($date, 0, 10) . '%');
                }
            });
        }
        //$results = $result->get()->toArray();
        $results = $query->get()->toArray();
        if (empty($results)) {
            return ResponseHelper::success($results, null, 'No requests found for the user', 200);
        }
        return ResponseHelper::success($results, null, 'All requests', 200);
    }


    public function store(StoreRequestRequest $request)
    {
        try {
            DB::beginTransaction();
            $requests = Request::query()
                ->create([
                    'user_id' => Auth::id(),
                    'title' => $request->title,
                    'type' => $request->type,
                    'date' => Carbon::now(),
                    'description' => $request->description,
                    'status' => 'waiting'
                ]);
            DB::commit();
            return ResponseHelper::created($requests, 'Request created successfully');
        } catch (\Exception $e) {
            DB::rollback();
            return ResponseHelper::error('Failed to create request.', null);
        }
    }


    public function show()
    {
        $result = Request::query()
            ->where('user_id', Auth::user()->id)
            ->get()
            ->toArray();
        if (empty($result)) {
            return ResponseHelper::success($result, 'Request not exist');
        }
        return ResponseHelper::success($result, 'My requests:');
    }


    public function getRequest($request, $date)
    {
        $result = Request::query()
            ->where('id', $request)
            ->with('user:id,first_name,last_name')
            ->with('user.department:id,name');
        if (!empty($date)) {
            $result->where(function ($query) use ($date) {
                if (strlen($date) == 4) {
                    $query->where('date', 'like', $date . '%');
                }
                if (strlen($date) == 7) {
                    $query->orWhere('date', 'like', substr($date, 0, 7) . '%');
                }
                if (strlen($date) == 10) {
                    $query->orWhere('date', 'like', substr($date, 0, 10) . '%');
                }
            });
        }
        $results = $result->get()->toArray();
        if (empty($results)) {
            return ResponseHelper::success($results, null, 'No requests found for the user', 200);
        }
        return ResponseHelper::success($results, 'My requests:');
    }


    public function update($request, $id)
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
            return ResponseHelper::error('You cannot update this request');
        }
    }


    public function destroy(Request $request)
    {
        if ($request->exists() && $request->status == 'waiting') {
            $request->delete();
            return ResponseHelper::deleted('Request deleted successfully');
        } else {
            return ResponseHelper::error('You cannot delete this request', null, 'error', 403);
        }
    }


    public function acceptRequest($request)
    {
        try {
            return DB::transaction(function () use ($request) {
                $request->update([
                    'status' => 'accepted'
                ]);
                if ($request->type == 'advanced') {
                    $user = User::find($request->user_id);
                    $salary = $user->salary;
                    $result = Decision::query()->create([
                        'user_id' => $request->user_id,
                        'type' => 'advanced',
                        'amount' => ($salary / 2),
                        'dateTime' => $request->dateTime,
                        'salary' => $salary
                    ]);
                }
                return ResponseHelper::updated([
                    'message' => 'Request accepted successfully',
                ]);
            });
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), $e->getCode());
        }
    }


    public function rejectRequest($request)
    {
        $existingRequest = Request::find($request);

        if ($existingRequest) {
            $existingRequest->update([
                'status' => 'rejected'
            ]);

            return ResponseHelper::success([
                'message' => 'Request rejected successfully',
            ]);
        } else {
            return ResponseHelper::error('Request not found', null, 'error', 404);
        }

    }


    public function sendRequest($request)
    {
        try {
            return DB::transaction(function () use ($request) {
                $validate = $request->validated();

                if ($validate['duration'] == 'hourly') {
                    $startVac = Carbon::parse($validate['startDate']);
                    $endVac = Carbon::parse($validate['endDate']);
                    $hoursNumber = $startVac->diffInHours($endVac);

                    $newRequest = Absences::create([
                        'user_id' => $validate['user_id'],
                        'startDate' => $startVac,
                        'endDate' => $endVac,
                        'duration' => $validate['duration'],
                        'hours_num' => $hoursNumber,
                    ]);
                } elseif ($validate['duration'] == 'daily') {
                    $newRequest = Absences::create([
                        'user_id' => $validate['user_id'],
                        'startDate' => $validate['startDate'],
                        'endDate' => $validate['endDate'],
                        'duration' => $validate['duration'],
                    ]);
                } else {
                    return ResponseHelper::error($validate, null, 'Error sending the request', 400);
                }

                return ResponseHelper::created($newRequest, 'Request sent successfully');
            });
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), $e->getCode());
        }
    }

}
