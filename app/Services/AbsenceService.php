<?php

namespace App\Services;

use App\Models\User;
use App\Models\Absences;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Helper\ResponseHelper;
use function Symfony\Component\String\s;

class AbsenceService
{
    public function index(Request $request)
    {
        $branchId = $request->input('branch_id');
        if ($request->has('date')) {
            $dateInput = request()->input('date');
            $year = substr($dateInput, 0, 4);
            $month = substr($dateInput, 5, 2);
        } else {
            $year = Carbon::now()->format('Y');
            $month = Carbon::now()->format('m');
        }
        $user = User::query()->where('branch_id', $branchId)->with('userInfo')->get();
        $results = [];
        foreach ($user as $item) {
            $justified = $item->absences()
                ->where('type', 'justified')
                ->whereYear('startDate', $year)
                ->whereMonth('startDate', $month)->count();
            $unjustified = $item->absences()
                ->where('type', 'Unjustified')
                ->whereYear('startDate', $year)
                ->whereMonth('startDate', $month)
                ->count();
            $sick = $item->absences()
                ->where('type', 'sick')
                ->whereYear('startDate', $year)
                ->whereMonth('startDate', $month)
                ->count();
            $results[] = [
                'id' => $item->id,
                'username' => $item->first_name,
                'lastname' => $item->last_name,
                'specialization' => $item->specialization,
                'userDepartment' => $item->department,
                'userUnjustified' => $unjustified,
                'sick' => $sick,
                'userJustified' => $justified,
                'all' => $unjustified + $justified + $sick,
                'userinfo' => $item->userInfo
            ];
        }
        return ResponseHelper::success($results);
    }

    public function show(User $user)
    {
        $userAbcences = $user->absences()->get('startDate')->toArray();
        return $userAbcences;
    }

    public function update($request)
    {
        Absences::query()
            ->findOrFail($request['id'])
            ->update($request);

        return 'updated successfully';
    }

    public function getDailyAbsence(Request $request, $branch)
    {
        $today = Carbon::now();
        if ($today->eq($request->date)) {
        } else {
            $dateInput = request()->input('date');
            $day = substr($dateInput, 8, 2);
            $user = User::query()->where('branch_id', $branch)->get();
            $result = $user->with('absences')
                ->whereDay('startDate', $day)->get();
            return $result;
        }
    }

    public function storeAbsence(Request $request)
    {
        $new_abs = Absences::create([
            'type' => $request->type,
            'user_id' => $request->user_id,
            'startDate' => $request->startDate,
            'isPaid' => $request->type == 'sick' ? true : $request->isPaid
        ]);
        return $new_abs;
    }

    public function getAbsences($user)
    {
        $absences = Absences::where('user_id', $user)->get();
        $groupedAbsences = $absences->groupBy('type')->toArray();
        $result = [
            'justified' => $groupedAbsences['justified'] ?? [],
            'unjustified' => $groupedAbsences['Unjustified'] ?? [],
            'sick' => $groupedAbsences['sick'] ?? [],
        ];
        return $result;
    }

    public function deleteAbsence($absence)
    {
        $result = Absences::find($absence);
        if (!$result) {
            return $result = 'Absence not found';
        }
        $result->update([//???
            'type' => 'null'
        ]);
        return $result = 'Absence deleted successfully';
    }

    public function store_absence($request)
    {
        $request->validated();
        foreach ($request->absence as $item) {
            $new_abs = Absences::create([
                'user_id' => $item['user_id'],
                'startDate' => $item['date'],
                'type' => $item['type'],
                'isPaid' => $item['type'] == 'sick' ? true : $item['isPaid']
            ]);
            return $results[] = $new_abs;
        }
        return false;
    }

    public function unjustifiedAbsence()
    {
        $absence = Absences::query()->where('type', 'Unjustified')
            ->where('status', 'waiting')->get()->toArray();
        return $absence;
    }

    public static function user_absence(Request $request)
    {
        $userId = $request->user_id;
        $date = $request->date;
        $year = null;
        $month = null;
        if (strlen($date) === 4) {
            $year = $date;
        } elseif (strlen($date) === 7) {
            $year = substr($date, 0, 4);
            $month = substr($date, 5, 2);
        }
        $result = User::query()
            ->where('id', $userId)
            ->with(['absences' => function ($query) use ($year, $month) {
                if ($year && !$month) {
                    $query->whereYear('startDate', $year);
                } elseif ($year && $month) {
                    $query->whereYear('startDate', $year)
                        ->whereMonth('startDate', $month);
                }
                $query->where('type', 'Unjustified');
            }])
            ->first();
        return $result;
    }

    public function AbsenceTypes($request)
    {
        $user = User::with(
            'UnPaidAbsences',
            'PaidAbsences',
            'sickAbsences'
            )->findOrFail($request->user_id);

        $paidabsences = $user->PaidAbsences;
        $unpaidabsences = $user->UnPaidAbsences;
        $sickabsences = $user->sickAbsences;

        return ['paidabsences'=>$paidabsences,
                'unpaidabsences'=>$unpaidabsences,
                'sickabsences'=>$sickabsences];
    }
}


