<?php

namespace App\Services;

use App\Models\User;
use App\Models\Absences;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Helper\ResponseHelper;

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
            $results[] = [
                'id' => $item->id,
                'username' => $item->first_name,
                'lastname' => $item->last_name,
                'specialization'=>$item->specialization,
                'userDepartment' => $item->department,
                'userUnjustified' => $unjustified,
                'userJustified' => $justified,
                'all' => $unjustified + $justified,
                'userinfo' => $item->userInfo
            ];
        }
        return ResponseHelper::success($results);
    }

       public function show(User $user)
    {

            $userAbcences = $user->absences()->get('startDate')->toArray();
            return  $userAbcences;

}
public function update(Request $request)
    {

            $result = Absences::query()
                ->where('id', $request->id)
                ->update(
                    [
                        'startDate' => $request->startDate,
                        'type' => $request->type
                    ]
                );
            if ($result) {
                return $resulls='updated successfully';

    }

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
    ]);
   
    return $new_abs;
}
public function getAbsences($user)
{
    $absences = Absences::where('user_id', $user)->get();
    $groupedAbsences = $absences->groupBy('type')->toArray();
   $result=[
        'justified' => $groupedAbsences['justified'] ?? [],
        'unjustified' => $groupedAbsences['Unjustified'] ?? [],
    ];
    return   $result;
}

public function deleteAbsence($absence)
{
    $result = Absences::find($absence);

    if (!$result) {
        return $result='Absence not found';
    }

    $result->update([
        'type' => 'null'
    ]);

    return  $result='Absence deleted successfully';
}
public function store_absence($request)
{
    $request->validated();

        foreach ($request->absence as $item) {
            $new_abs = Absences::create([
                'type' => $item['type'],
                'user_id' => $item['user_id'],
                'startDate' => $item['date'],
            ]);
           return $results[] = $new_abs;
        }
    }
    public function unjustifiedAbsence()
    {
        $absence = Absences::query()->where('type', 'null')->where('status', 'waiting')->get();
        return  $absence;
    }


}

