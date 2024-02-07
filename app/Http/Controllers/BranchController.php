<?php

namespace App\Http\Controllers;

use App\Helper\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\BranchRequest;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class BranchController extends Controller
{
    public function index()
    {
        $branches = Branch::withCount('users')->get()->toArray();
        return ResponseHelper::success($branches, null);
    }
    public function store(BranchRequest $request)
    {
        try {
            $validatedData = $request->validated();
            return DB::transaction(function () use ($validatedData) {
                $result = Branch::query()->create($validatedData);
                return ResponseHelper::success($result, null);
            });
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHelper::error($e->validator->errors()->first(), 400);
        } catch (\Illuminate\Database\QueryException $e) {
            return ResponseHelper::error('Invalid branch name or IP', 400);
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), $e->getCode());
        }
    }
    public function show($id)
    {
        $branch = Branch::query()->findOrFail($id);
        return ResponseHelper::success($branch, null);
    }
    public function update(Request $request, $id)
    {
        try {
            $branch = Branch::query()->findOrFail($id);
            return DB::transaction(function () use ($branch, $request) {
                $branch->update([
                    'name' => $request->name,
                    'fingerprint_scanner_ip' => $request->fingerprint_scanner_ip
                ]);
                return ResponseHelper::success('updated', null);
            });
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHelper::error($e->validator->errors()->first(), 400);
        } catch (\Illuminate\Database\QueryException $e) {
            return ResponseHelper::error('Invalid branch name or IP', 400);
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), $e->getCode());
        }
    }
    public function destroy(Request $request, $id)
    {
        try {
            $branch = Branch::query()->findOrFail($id);
            if (!Hash::check($request->password, Auth::user()->getAuthPassword())) {
                return ResponseHelper::error('not authorized', null);
            }
            return DB::transaction(function () use ($branch) {
                $branch->delete();
                return ResponseHelper::success('deleted');
            });
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), $e->getCode());
        }
    }

}
