<?php

namespace App\Services;

use App\Models\Late;
use App\Models\Policy;
use App\Models\User;
use App\Models\Absences;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AbsenceService
{


    protected $userTimeService;


    public function __construct(UserTimeService $userTimeService)
    {
        $this->userTimeService = $userTimeService;
    }

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

    //    public function getDailyAbsence(Request $request, $branch)
    //    {
    //        $today = Carbon::now();
    //        if ($today->eq($request->date)) {
    //        } else {
    //            $dateInput = request()->input('date');
    //            $day = substr($dateInput, 8, 2);
    //            $user = User::query()->where('branch_id', $branch)->get();
    //            $result = $user->with('absences')
    //                ->whereDay('startDate', $day)->get();
    //            return $result;
    //        }
    //    }

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


    public function addAbsence(Request $request)
    {
        $user = User::findOrFail($request->user_id);
        $result = Absences::updateOrCreate(

            [
                'user_id' => $user->id,
                'startDate' => Carbon::now()->format('Y-m-d'),
            ],

            [
                'type' => $request->type,
                'duration' => 'hourly',
                'isPaid' => $request->type == 'sick' ? true : $request->isPaid
            ]
        );
        return $result;
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
        $result->update([ //???
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
        $absence = Absences::query()
            ->whereHas('users', function ($query) {
                $query->where('role', '!=', 'admin');
            })
            ->where('type', 'Unjustified')
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

        return [
            'Paid' => $paidabsences,
            'UnPaid' => $unpaidabsences,
            'Sick' => $sickabsences
        ];
    }


    public static function user_absences(Request $request)
    {
        $result = User::query()
            ->where('role', '!=', 'admin')
            ->where('branch_id', $request->branch_id)
            ->with(
                'userInfo:id,image',
                'department',
                'allAbsences',
                'UnPaidAbsences',
                'PaidAbsences',
                'sickAbsences',
            )->withCount(
                'justifiedPaidAbsencesCount as justifiedPaid',
                'justifiedUnPaidAbsencesCount as justifiedUnPaid',
                'UnjustifiedPaidAbsencesCount as UnjustifiedPaid',
                'UnjustifiedUnPaidAbsencesCount as UnjustifiedUnPaid'
            )
            ->get()
            ->toArray();

        return $result;
    }


    public function allUserAbsences($request)
    {
        $user = User::query()
            ->where('role', '!=', 'admin')
            ->with('allAbsences')->findOrFail($request->user_id);
        return $user;
    }

    public function absenceStatus($user_id, $date)
    {
        return Absences::query()->where('user_id', $user_id)
            ->whereRaw('DATE(startDate) = ? ', [$date])
            ->latest()
            ->exists();
    }

    public function totalAbsenceHours($user_id, $date)
    {
        $user = User::query()->findOrFail($user_id);
        $latehours = Late::where('user_id', $user->id)
            ->where('demands_compensation', 1);
        $absence = Absences::where('user_id', $user->id)
            ->where('demands_compensation', 1);
        if ($date) {
            $late = $this->userTimeService->filterDate($latehours, $date, 'lateDate')->sum('hours_num');
            $absences = $this->userTimeService->filterDate($absence, $date, 'startDate')->count();
        }
        if (!$date) {
            $late = $latehours->sum('hours_num');
            $absences = $absence->count();
        }
        $branchpolicy = Policy::query()->where('branch_id', $user->branch_id)->first();
        if ($branchpolicy != null) {
            $startTime = Carbon::parse($branchpolicy->work_time['start_time']);
            $endTime = Carbon::parse($branchpolicy->work_time['end_time']);
            $worktime = $startTime->diffInMinutes($endTime, false);
            //  $worktime = $worktime%60;
            $absencehours = $absences * ($worktime / 60);
            $totalhours = $absencehours + $late;
        }
        return Carbon::parse($totalhours)->format('HH:MM');
    }
}
