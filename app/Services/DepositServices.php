<?php

namespace App\Services;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Deposit;
use Illuminate\Support\Facades\DB;
use App\Http\Traits\Files;

class DepositServices
{
    public function index($request) //all users with department and deposits
    {
        $branchId = $request->branch_id;
        $user = User::where('branch_id', $branchId);
        $results = $user
            ->with('department')
            ->with('deposits')
            ->get()
            ->toArray();
        if (empty($results)) {
          return  $result ='empty';
        }
        return  $results ;
    }

    public function store($request)
    {
        $validate = $request->validated();
            $path = Files::saveFile($request);
        return DB::transaction(function () use ( $request , $path) {
            $deposit = Deposit::query()->create([
                'title'=> $request->title,
                'name'=>$request->name,
                'description'=>$request->description,
                'user_id'=>$request->user_id,
                'received_date'=>$request->received_date,
                'path'=> $path,
            ]);
            return  $deposit;
        });
    }
    public function update($request, $id)
    {
        return DB::transaction(function () use ($request, $id) {
            Deposit::query()
                ->where('id', $id)
                ->update($request);
            return  $result ='Deposit has been updated';
        });
    }

    public function show()
    {
        $result = Deposit::query()->with('user', 'user.userInfo:id,user_id,image')
            ->get()
            ->toArray();
        return  $result;
    }

    public function destroy($id)
    {
        return DB::transaction(function () use ($id) {
            $deposit = Deposit::query()->find($id);
            $deposit->delete();
            return  $result = 'Deposit has been deleted';
        });
    }
}



