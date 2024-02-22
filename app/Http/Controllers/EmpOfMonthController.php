<?php

namespace App\Http\Controllers;

use App\Helper\ResponseHelper;
use App\Models\EmpOfMonth;
use App\Http\Requests\StoreEmpOfMonthRequest;
use App\Http\Requests\UpdateEmpOfMonthRequest;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\DB;

class EmpOfMonthController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(HttpRequest $request) //show all employees of the monthes
    {
        $branchId = $request->input('branch_id');
        $result = EmpOfMonth::query()
            ->with('user','user.userInfo')->whereHas('user', function ($query) use ($branchId) {
                $query->where('branch_id', $branchId); })->get()->toArray();
        return ResponseHelper::success($result, null);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEmpOfMonthRequest $request)
    {
        $validate = $request->validated();

        return DB::transaction(function() use ($validate){
            $existingEmpOfMonth = EmpOfMonth::where('date', now()->format('Y-m'))
            ->first();

        if ($existingEmpOfMonth) {
            $result = EmpOfMonth::query()->update([
                'user_id' => $validate['user_id'],
            ]);
            return ResponseHelper::updated('updated');
        }
        // return DB::transaction(function () use ($validate) {
        $result = EmpOfMonth::query()->create([
            'user_id' => $validate['user_id'],
            'date' => now()->format('Y-m'),
        ]);
        return ResponseHelper::success($result, null);
        });

        return ResponseHelper::error('error', null);
        // $result = EmpOfMonth::create([
        //     'user_id' => $validate['user_id'],
        //     'date' => now()->format('Y-m'),
        // ]);
        // return ResponseHelper::success($result, null);
    }

    /**
     * Display the specified resource.
     */
    public function show(HttpRequest $request) //show emp for current month
    {
        $branchId =  $request->branch_id;
                $result = EmpOfMonth::query()
            ->where('date', now()->format('Y-m'))
            ->with('user','user.userInfo')->whereHas('user', function ($query) use ($branchId) {
                $query->where('branch_id', $branchId); })->first();
        return ResponseHelper::success($result, null);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(EmpOfMonth $empOfMonth)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEmpOfMonthRequest $request, EmpOfMonth $empOfMonth)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EmpOfMonth $empOfMonth)
    {
        //
    }
}
