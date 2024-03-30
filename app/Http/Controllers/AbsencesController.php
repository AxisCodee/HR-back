<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Absences;
use Illuminate\Http\Request;
use App\Helper\ResponseHelper;
use App\Services\AbsenceService;
use App\Services\UserTimeService;
use App\Http\Requests\AbsencesRequest\StoreAbsencesRequest;
use App\Http\Requests\AbsencesRequest\UpdateAbsencesRequest;
use Illuminate\Support\Facades\Auth;

class AbsencesController extends Controller
{
    protected $absenceService;
    protected $usertimeService;

    public function __construct(AbsenceService $absenceService, UserTimeService $usertimeService)
    {
        $this->absenceService = $absenceService;
        $this->usertimeService = $usertimeService;
    }

    // public function index(Request $request)
    // {
    //     return $this->absenceService->index($request);
    // }

    public function show(User $user)
    {
        $result = $this->absenceService->show($user);
        return ResponseHelper::success($result, null, 'Absence');
    }

    public function update(UpdateAbsencesRequest $request)
    {
        $password = Auth::user()->ownerPassword;
        if ($password == null || $request->password != $password) {
            return ResponseHelper::error('You are not authorized');
        }
        $result = $this->absenceService->update($request->toArray());
        return ResponseHelper::success($result, null, 'Absence updated successfully');
    }


    public function addAbsence(UpdateAbsencesRequest $request)
    {
        $password = Auth::user()->ownerPassword;
        if ($password == null || $request->password != $password) {
            return ResponseHelper::error('You must be admin ^_^');
        }
        $result = $this->absenceService->addAbsence($request);

        return ResponseHelper::success($result, null, 'Absence updated successfully');
    }

    public function getDailyAbsence(Request $request, $branch)
    {
        $result = $this->absenceService->getDailyAbsence($request, $branch);
        return ResponseHelper::success($result, null, 'daily absence');
    }

    //without return
    public function unjustifiedAbsence()
    {
        $absence = $this->absenceService->unjustifiedAbsence();
        return ResponseHelper::success($absence, null);
    }

    public function store_absence(StoreAbsencesRequest $request) //store multi
    {
        $request->validated();
        $results = $this->absenceService->store_absence($request);
        return ResponseHelper::success($results, null, 'Absence added successfully');
    }

    public function storeAbsence(Request $request) //store one
    {
        $result = $this->absenceService->storeAbsence($request);
        return ResponseHelper::success($result, null, 'Absence added successfully');
    }

    public function getAbsences($user)
    {
        return $this->absenceService->getAbsences($user);
    }

    public function deleteAbsence($absence)
    {
        $result = $this->absenceService->deleteAbsence($absence);
        return ResponseHelper::success(null, null, $result);
    }


    public function getUserAbsence(Request $request)
    {
        $result = $this->absenceService->user_absence($request);
        if ($result) {
            return ResponseHelper::success($result, null);
        } else {
            return ResponseHelper::error('No results found', 404);
        }
    }

    public function absenceTypes(Request $request)
    {
        $validate = $request->validate([
            'user_id' => ['required', 'exists:users,id', 'integer'],
        ]);

        $absence = $this->absenceService->AbsenceTypes($request);

        return ResponseHelper::success(
            $absence,
            null
        );
    }

    public function getUserAbsences(Request $request)
    {
        $result = $this->absenceService->user_absences($request);
        if ($result) {
            return ResponseHelper::success($result, null);
        } else {
            return ResponseHelper::error('No results found', 404);
        }
    }

    public function allUserAbsences(Request $request)
    {
        $result = $this->absenceService->allUserAbsences($request);
        if ($result) {
            return ResponseHelper::success($result, null);
        } else {
            return ResponseHelper::error('No results found', 404);
        }
    }



}
