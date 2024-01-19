<?php

namespace App\Http\Controllers;

use App\Models\Absences;
use App\Helper\ResponseHelper;
use App\Models\User;
use App\Models\Date;
use App\Models\DatePIn;
use App\Http\Requests\StoreAbsencesRequest;
use App\Http\Requests\UpdateAbsencesRequest;
use App\Models\Attendance;
use Carbon\Carbon;

class AbsencesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

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
    public function getAbsence()
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMont=  Carbon::now()->endOfMonth();


    $times=Date::query()->whereBetween('date',[$startOfMonth,$endOfMont])->get()->toArray();
      $users=User::query()->get()->toArray();
      foreach($times as $time )
      {
        foreach($users as $user)
        {

        $userDate=DatePin::query()->where('pin',$user['pin'])->where('date_id',$time['id'])->get()->toArray();
        //$userDate=DatePin::query()->where('pin',6)->where('date_id',24)->get()->toArray();
        if(!$userDate)
        {
        $absences=Absences::where('user_id',$user['id'])->where('status','accepted')->get()->toArray();
        if(!$absences)
        {
          $results=$user->absences()->create(
            [
            'startDate'=>$time->date,
            'endDatee'=>null,
            'statuse'=>'waiting'


            ]

            );

        }
    }
        }
      }


      return ResponseHelper::success($results,'yaaaaaa', null);

    }
}
