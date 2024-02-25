<?php

namespace App\Http\Controllers;

use App\Http\Requests\DepositRequest;
use App\Models\Deposit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helper\ResponseHelper;
use App\Http\Requests\DepositRequest\UpdateDepositRequest;
use App\Http\Requests\DepositRequest\StoreDepositRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;


class DepositController extends Controller
{

    public function index(Request $request) //all users with department and deposits
    {
        $branchId = $request->input('branch_id');
        $user = User::where('branch_id', $branchId);
        $results = $user
            ->with('department')
            ->with('deposits')
            ->get()
            ->toArray();
        if (empty($results)) {
            return ResponseHelper::success('empty');
        }
        return ResponseHelper::success($results, null);
    }

    public function store(StoreDepositRequest $request)
    {
        $validate = $request->validated();
        return DB::transaction(function () use ($validate) {
            $deposit = Deposit::query()->create($validate);
            return ResponseHelper::success($deposit, null);
        });
        return ResponseHelper::error('error', null);
    }

    public function update(UpdateDepositRequest $request, $id)
    {
        $validate = $request->validated();
        return DB::transaction(function () use ($validate, $id) {
            Deposit::query()
                ->where('id', $id)
                ->update($validate);
            return ResponseHelper::success('Deposit has been updated', null);
        });
        return ResponseHelper::error('error', null);
    }
    public function show() //all deposits
    {
        $result = Deposit::query()->with('user', 'user.userInfo:id,user_id,image')
            ->get()
            ->toArray();
        return ResponseHelper::success($result, null);
    }

    public function destroy($id)
    {
        try {
            return DB::transaction(function () use ($id) {
                $deposit = Deposit::query()->find($id);
                $deposit->delete();
                return ResponseHelper::success('Deposit has been deleted', null);
            });
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), $e->getCode());
        }
        //return ResponseHelper::error('not deleted', null);
    }
}
