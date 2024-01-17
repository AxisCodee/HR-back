<?php

namespace App\Http\Controllers;

use App\Helper\ResponseHelper;
use App\Http\Requests\AddressRequest;
use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class AddressController extends Controller
{

    public function store(AddressRequest $request)
    {
        $validate = $request->validated();
        return DB::transaction(function () use ($validate) {
            $address = Address::query()->updateOrCreate($validate);
            //dd($validate);
            return ResponseHelper::success($address, null);
        });
        return ResponseHelper::error('error', null);
    }
    public function destroy(Request $request)
    {
        return DB::transaction(function () use ($request) {
            $address = Address::query()->find($request->address_id);
            $address->delete();
            return ResponseHelper::success('Address has been deleted', null);
        });
        return ResponseHelper::error(['not deleted'], null);
    }
}
