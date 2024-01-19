<?php

namespace App\Http\Controllers;

use App\Helper\ResponseHelper;
use App\Http\Requests\AddressRequest;
use App\Http\Requests\UpdateAddressRequest;
use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class AddressController extends Controller
{

    public function store(AddressRequest $request)
    {
        $validate = $request->validated();
        return DB::transaction(function () use ($validate) {
            $address = Address::query()->create($validate);
            return ResponseHelper::success($address, null);
        });
        return ResponseHelper::error('error', null);
    }
    public function update(UpdateAddressRequest $request, $id)
    {
        $validate = $request->validated();
        return DB::transaction(function () use ($validate, $id) {
            Address::query()
                ->where('id', $id)
                ->update($validate);
            return ResponseHelper::success('Address updated successfuly', null);
        });
        return ResponseHelper::error('error', null);
    }

    public function destroy($id)
    {
        return DB::transaction(function () use ($id) {
            $address = Address::query()->find($id);
            $address->delete();
            return ResponseHelper::success('Address has been deleted', null);
        });
        return ResponseHelper::error('not deleted', null);
    }
}
