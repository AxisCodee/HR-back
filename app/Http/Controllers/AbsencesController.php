<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Absences;
use Illuminate\Http\Request;
use App\Helper\ResponseHelper;
use App\Services\AbsenceService;
use App\Services\UserTimeService;
use App\Http\Requests\AbsencesRequest\StoreAbsencesRequest;

class AbsencesController extends Controller
{
    protected $absenceService;
    protected $usertimeService;

    public function __construct(AbsenceService $absenceService,UserTimeService $usertimeService)
    {
        $this->absenceService = $absenceService;
        $this->usertimeService = $usertimeService;

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

    public function AbsenceTypes(Request $request)
    {
        $validate = $request->validate([
            'user_id'=> ['required','exists:users,id','integer'],
            // 'date'=>['before_or_equal:today'],
        ]);

        $user = User::with(
            'justifiedUnPaidAbsences',
            'justifiedPaidAbsences',
            'unJustifiedPaidAbsences',
            'unJustifiedUnPaidAbsences',
            'sickAbsences')->findOrFail($request->user_id);

        $paidabsences = $user->justifiedPaidAbsences->merge($user->unJustifiedPaidAbsences);
        $unpaidabsences = $user->justifiedUnPaidAbsences->merge($user->unJustifiedUnPaidAbsences);
        $sickabsences = $user->sickAbsences;

        return ResponseHelper::success([
            'Paid'=>$user,
            'Unpaid'=>$unpaidabsences,
            'Sick'=>$sickabsences,
            // 'Paid' =>$this->usertimeService->filterDate($paidabsences,$request->date,'startDate'),
            // 'Unpaid'=>$this->usertimeService->filterDate($unpaidabsences,$request->date,'startDate'),
            // 'sick'=>$this->usertimeService->filterDate($sickabsences,$request->date,'startDate'),
        ], null);
        // $absence = Absences::where('user_id',$request->user_id)
        //                     ->where;

    }
}

