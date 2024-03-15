<?php

namespace App\Http\Controllers;

use App\Models\Date;
use App\Http\Requests\DateRequest\StoreDateRequest;
use App\Http\Requests\DateRequest\UpdateDateRequest;

class DateController extends Controller
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
    public function store(StoreDateRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Date $date)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDateRequest $request, Date $date)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Date $date)
    {
        //
    }
}
