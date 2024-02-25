<?php

namespace App\Http\Controllers;

use App\Models\DatePin;
use App\Http\Requests\DatePinRequest\StoreDatePinRequest;
use App\Http\Requests\DatePinRequest\UpdateDatePinRequest;

class DatePinController extends Controller
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
    public function store(StoreDatePinRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(DatePin $datePin)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDatePinRequest $request, DatePin $datePin)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DatePin $datePin)
    {
        //
    }
}
