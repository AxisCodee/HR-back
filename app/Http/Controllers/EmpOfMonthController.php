<?php

namespace App\Http\Controllers;

use App\Helper\ResponseHelper;
use App\Models\EmpOfMonth;
use App\Http\Requests\StoreEmpOfMonthRequest;
use App\Http\Requests\UpdateEmpOfMonthRequest;
use Illuminate\Support\Facades\DB;

class EmpOfMonthController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index() //show all employees of the monthes
    {
        $result = EmpOfMonth::query()
            ->with('user')->get()->toArray();
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

        return DB::transaction(function () use ($validate) {
            $result = EmpOfMonth::query()->updateOrCreate([
                'date' => now()->format('Y-m'),
            ],
        [                'user_id' => $validate['user_id'],
    ]);

            return ResponseHelper::success($result, null);
        });
    }

    /**
     * Display the specified resource.
     */
    public function show() //show emp for current month
    {
        $result = EmpOfMonth::query()
            ->where('date', now()->format('Y-m'))
            ->with('user')->first();
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
