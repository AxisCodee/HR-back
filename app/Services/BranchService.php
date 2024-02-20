<?php

namespace App\Services;


use App\Helper\ResponseHelper;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;


class BranchService
{
    public function index()
    {
        $branches = Branch::withCount('users')->get()->toArray();
        return $branches;
    }

    public function store($request)
    {
        $validatedData = $request->validated();
        return DB::transaction(function () use ($validatedData) {
            $result = Branch::query()->create($validatedData);
            return ResponseHelper::success($result, null);
        });
    }

    public function show($id)
    {
        $branch = Branch::query()->findOrFail($id);
        return $branch;
    }

    public function update(Request $request, $id)
    {
        $branch = Branch::query()->findOrFail($id);
        return DB::transaction(function () use ($branch, $request) {
            $branch->update([
                'name' => $request->name,
                'fingerprint_scanner_ip' => $request->fingerprint_scanner_ip
            ]);
            return ResponseHelper::success('Updated successfuly', null);
        });
    }

    public function destroy($request, $id)
    {
        $branch = Branch::query()->findOrFail($id);
        if (!Hash::check($request->password, Auth::user()->getAuthPassword())) {
            return ResponseHelper::error('not authorized', null);
        }
        return DB::transaction(function () use ($branch) {
            $branch->delete();
            return ResponseHelper::success('deleted');
        });
    }
}
