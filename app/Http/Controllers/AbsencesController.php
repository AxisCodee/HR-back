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


      $thisMonth=Date::query()->get();
      foreach($thisMonth as $item )
      {
        $users=User::query()->get('pin')->toArray();
        $thisday=DatePin::query()
        ->whereBetween('date',[$startOfMonth,$endOfMont])
        ->get()->toArray();

        dd($thisday);

      }


      return ResponseHelper::success( $thisday,'Address has been deleted', null);

    }
}
