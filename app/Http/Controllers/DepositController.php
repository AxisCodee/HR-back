<?php

namespace App\Http\Controllers;

use App\Http\Requests\DepositRequest;
use App\Models\Deposit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helper\ResponseHelper;



class DepositController extends Controller
{
    public function store(DepositRequest $request)
    {
        $validate = $request->validated();
        return DB::transaction(function () use ($validate) {
            $deposit = Deposit::query()->updateOrCreate($validate);
            return ResponseHelper::success($deposit, null);
        });
        return ResponseHelper::error(['error'], null);
    }
    public function destroy(Request $request)
    {
        return DB::transaction(function () use ($request) {
            $deposit = Deposit::query()->find($request->deposit_id);
            $deposit->delete();
            return ResponseHelper::success('Deposit has been deleted', null);
        });
        return ResponseHelper::error(['not deleted'], null);
    }
}
