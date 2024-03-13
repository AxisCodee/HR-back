<?php

namespace App\Http\Controllers;

use App\Models\Deposit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helper\ResponseHelper;
use App\Http\Requests\DepositRequest\UpdateDepositRequest;
use App\Http\Requests\DepositRequest\StoreDepositRequest;
use App\Models\User;
use App\Services\DepositServices;

class DepositController extends Controller
{
    protected $DepositServices;

    public function __construct(DepositServices $DepositServices)
    {
        $this->DepositServices = $DepositServices;
    }


    public function index(Request $request) //all users with department and deposits
    {
        $result = $this->DepositServices->index($request);
        return ResponseHelper::success($result, null);

    }

    public function store(StoreDepositRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $result = $this->DepositServices->store($request);
            return ResponseHelper::success($result, null);
        });
    }

    public function update(UpdateDepositRequest $request, $id)
    {
        $validate = $request->validated();
        return DB::transaction(function () use ($validate, $id) {
            $result = $this->DepositServices->update($validate, $id);
            return ResponseHelper::success($result, null);
        });
    }

    public function show() //all deposits
    {
        $result = $this->DepositServices->show();
        return ResponseHelper::success($result, null);
    }

    public function destroy($id)
    {
        return DB::transaction(function () use ($id) {
            $result = $this->DepositServices->destroy($id);
            return ResponseHelper::success('Deposit has been deleted', null);
        });
    }
}
