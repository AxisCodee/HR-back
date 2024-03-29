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


    public function show(User $user)
    {
        $userAbcences = $user->absences()->get()->toArray();
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

        return ['Paid'=>$paidabsences,
                'UnPaid'=>$unpaidabsences,
                'Sick'=>$sickabsences];
    }



    public static function user_absences(Request $request)
    {
        $result = User::query()
        ->with('userInfo:id,image'
        , 'department',
        'allAbsences'
        ,'UnPaidAbsences',
         'PaidAbsences',
         'sickAbsences',
         )->withCount('justifiedPaidAbsencesCount as justifiedPaid'
         ,'justifiedUnPaidAbsencesCount as justifiedUnPaid'
         ,'UnjustifiedPaidAbsencesCount as UnjustifiedPaid'
         ,'UnjustifiedUnPaidAbsencesCount as UnjustifiedUnPaid')
        ->get()
        ->toArray();

    return $result;
    }



    public function allUserAbsences($request)
    {
        $user = User::with('allAbsences')->findOrFail($request->user_id);
        return $user;
    }

}


