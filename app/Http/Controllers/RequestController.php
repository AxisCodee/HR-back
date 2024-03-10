<?php

namespace App\Http\Controllers;

use App\Models\Request;
use App\Http\Requests\RequestRequest\StoreRequestRequest;
use App\Http\Requests\RequestRequest\UpdateRequestRequest;
use App\Helper\ResponseHelper;
use App\Http\Requests\RequestRequest\SendRequest;
use App\Services\RequestService;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Auth;

class RequestController extends Controller
{

    protected $requestService;

    public function __construct(RequestService $requestService)
    {
        $this->requestService = $requestService;
    }

    public function index()
    {
        return $this->requestService->index();
    }

    public function store(StoreRequestRequest $request)
    {
        return $this->requestService->store($request);
    }

    public function show()
    {
        return $this->requestService->show();
    }

    public function getRequest($request)
    {
        return $this->requestService->getRequest($request);
    }


    public function update(UpdateRequestRequest $request, $id)
    {
        return $this->requestService->update($request, $id);
    }

    public function destroy(Request $request)
    {
        return $this->requestService->destroy($request);
    }

    public function acceptRequest(Request $request)
    {
        return $this->requestService->acceptRequest($request);
    }

    public function rejectRequest($request)
    {
        return $this->requestService->rejectRequest($request);
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

    public function getComplaints(HttpRequest $request)
    {
        $branchId = $request->branch_id;
        $result = Request::with('user.department', 'user.userInfo:id,user_id,image')->with('user', function ($query) use ($branchId) {
            $query->where('branch_id', $branchId);
        })
            ->where('type', 'complaint')
            ->get()
            ->toArray();

            
        if (empty($result)) {
            return ResponseHelper::success($result);
        }

        return ResponseHelper::success($result, null, 'complaint', 200);
    }

    public function sendRequest(SendRequest $request)
    {
        return $this->requestService->sendRequest($request);
    }

    public function deleteComplaints($request)
    {
        if ($request->type == 'complaint') {
            $request->delete();
            return ResponseHelper::success(null, null, 'deleted successfully');
        } else {
            return ResponseHelper::success(null, null, 'can not deleted');
        }

    }
}
