<?php

namespace App\Http\Controllers;

use App\Models\Absences;
use App\Http\Requests\StoreAbsencesRequest;
use App\Http\Requests\UpdateAbsencesRequest;

class AbsencesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
}
