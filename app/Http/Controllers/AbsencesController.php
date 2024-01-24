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
    public function index()
    {
        $currentDateOfMonth = Carbon::now()->format('y','m');
      
        $user=User::query()->get();

        foreach($user as $item)
        {

           $Unjustified= $item->absences()->where('type','justified')->count();
            $justified=$item->absences()->where('type','Unjustified')->count();


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
    public function show(Absences $absences)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAbsencesRequest $request, Absences $absences)
    {
        //
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






