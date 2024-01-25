<?php

namespace App\Http\Controllers;

use App\Models\Late;
use App\Http\Requests\StoreLateRequest;
use App\Http\Requests\UpdateLateRequest;

class LateController extends Controller
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
    public function store(StoreLateRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Late $late)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateLateRequest $request, Late $late)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Late $late)
    {
        //
    }
}
