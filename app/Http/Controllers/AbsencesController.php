<?php

namespace App\Http\Controllers;

use App\Helper\ResponseHelper;
use App\Http\Requests\StoreAbsencesRequest;
use App\Http\Requests\UpdateAbsencesRequest;
use App\Models\Absences;
use App\Models\User;
use App\Models\Date;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AbsencesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        if($request->has('date'))
        {
$dateInput =request()->input('date');
  $year = substr($dateInput, 0, 4);
   $month = substr($dateInput, 5, 2);
        }
        else
        {
            $year = Carbon::now()->format('Y');
            $month = Carbon::now()->format('m');

        }
        $user=User::query()->get();

        foreach($user as $item)
        {

           $justified= $item->absences()
           ->where('type','justified')
           ->whereYear('startDate',$year )
           ->whereMonth('startDate',$month)->count();
            $Unjustified=$item->absences()
            ->where('type','Unjustified')
            ->whereYear('startDate',$year )
            ->whereMonth('startDate',$month)
            ->count();



           $results[]= $result=
            [
            'id'=>$item->id,
            'username'=>$item->first_name,
            'userDepartment'=>$item->department,
           'userUnjustified'=> $Unjustified,
           'userjustified'=>   $justified,
           'all'=>$Unjustified+ $justified
            ];
        }
            return ResponseHelper::success($results);
        }




    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAbsencesRequest $request)
    {

    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        $userAbcences=$user->absences()->get('startDate')->toArray();
        return ResponseHelper::success($userAbcences);

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAbsencesRequest $request, Absences $absences)
    {
        $result=$absences->update(
            [
                'startDate'=>$request->startDate
            ]
            );
            return ResponseHelper::success($result);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Absences $absences)
    {
        //
    }
    public function getDailyAbsence(Request $request)
    {
        $today = Carbon::now();
        if($today->eq($request->date))
        {
            $this->cuurentAbsence();
        }
        else{
            $dateInput =request()->input('date');
            $day = substr($dateInput, 8, 2);
            $user=User::query()->get();
     $result=$user->with('absences')
     ->whereDay('startDate',$day)->get();
     return ResponseHelper::success($result);

    }
}
    public function cuurentAbsence()
    {

          $usersWithoutAttendance = DB::table('users')
            ->leftJoin('attendances', function ($join) {
                $join->on('users.pin', '=', 'attendances.pin')
                    ->whereDate('attendances.datetime', '=', Carbon::now()->format('y,m,d'));
            })
            ->whereNull('attendances.pin')
            ->select('users.*')
            ->get();

        return ResponseHelper::success($usersWithoutAttendance, 'yaaaaaa', null);

    }
}






