<?php

namespace App\Services;

use Exception;
use App\Models\User;
use App\Models\Deposit;
use App\Http\Traits\Files;
use Illuminate\Http\Request;
use App\Helper\ResponseHelper;
use Illuminate\Support\Facades\DB;

class DepositServices
{
    protected $fileService;


    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }
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
            return $result = 'empty';
        }
        return $results;
    }

    public function store($request)
    {
        return DB::transaction(function () use ($request) {
            $path = null;
            if ($request->hasFile('path')) {
                $path = $this->fileService->upload($request->file('path'), 'file');
            }
            $deposit = Deposit::query()->create([
                'title' => $request->title,
                'name' => $request->name,
                'description' => $request->description,
                'user_id' => $request->user_id,
                'received_date' => $request->received_date,
                'path' => $path,
            ]);
            return $deposit;
        });
    }

    public function update($request, $id)
    {
        return DB::transaction(function () use ($request, $id) {
            Deposit::query()
                ->where('id', $id)
                ->update($request->validated());
            return 'Deposit has been updated';
        });
    }

    public function show()
    {
        return Deposit::query()->with('user', 'user.userInfo:id,user_id,image')
            ->get()
            ->toArray();
    }

    public function destroy($id)
    {
        $deposit = Deposit::query()->find($id);
        if ($deposit == null) {
            return ResponseHelper::error('Deposit not found');
        }
        $deposit->delete();
        return 'Deposit has been deleted';
    }

}



