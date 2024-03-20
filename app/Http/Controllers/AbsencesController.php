<?php

namespace App\Http\Controllers;

use App\Helper\ResponseHelper;
use App\Http\Requests\AbsencesRequest\StoreAbsencesRequest;
use App\Models\User;
use App\Services\AbsenceService;
use Illuminate\Http\Request;

class AbsencesController extends Controller
{
    protected $absenceService;

    public function __construct(AbsenceService $absenceService)
    {
        $this->absenceService = $absenceService;
    }

    public function index(Request $request)
    {
        return $this->absenceService->index($request);
    }

    public function show(User $user)
    {
        try {
            $result = $this->absenceService->show($user);
            return ResponseHelper::success($result, null, 'Absence');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), $e->getCode());
        }
    }

    public function update(Request $request)
    {
        try {
            $result = $this->absenceService->update($request);
            return ResponseHelper::success($result, null, 'Absence updated successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), $e->getCode());
        }
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

    public function store_absence(StoreAbsencesRequest $request)//store multi
    {
        try {
            $request->validated();
            $results = $this->absenceService->store_absence($request);
            return ResponseHelper::success($results, null, 'Absence added successfully');
        } catch (\Throwable $e) {
            return ResponseHelper::error($e);
        }
    }

    public function storeAbsence(Request $request)//store one
    {
        try {
            //   $request->validated();
            $result = $this->absenceService->storeAbsence($request);
            return ResponseHelper::success($result, null, 'Absence added successfully');
        } catch (\Throwable $e) {
            return ResponseHelper::error($e);
        }
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

    public function getUserAbsences(Request $request)
    {
        $result = $this->absenceService->user_absences($request);
        if ($result) {
            return ResponseHelper::success($result, null);
        } else {
            return ResponseHelper::error('No results found', 404);
        }

    }
}

