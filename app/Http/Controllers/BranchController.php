<?php

namespace App\Http\Controllers;

use App\Helper\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class BranchController extends Controller
{
    public function index()
    {
        try {
            $branches = Branch::withCount('users')->get()->toArray();
            return ResponseHelper::success($branches, null);
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), $e->getCode());
        }
    }
    public function index()
    {
        try {
            $branches = Branch::withCount('users')->get()->toArray();
            return ResponseHelper::success($branches, null);
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == 23000) {
                return ResponseHelper::error('Cannot store duplicate branch name', 400);
            } else {
                return ResponseHelper::error($e->getMessage(), $e->getCode());
            }
        } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
            return ResponseHelper::error('Cannot store duplicate branch name', 400);
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
        $branch = Branch::query()->findOrFail($id);
        $branch->update([
            'name' => $request->name
        ]);
        return ResponseHelper::success('updated', null);
    }
    public function destroy(Request $request, $id)
    {
        $branch = Branch::query()->findOrFail($id);
        if (!Hash::check($request->password, Auth::user()->getAuthPassword())) {
            return ResponseHelper::error('not authorized', null);
        }
        $branch->delete();
        return ResponseHelper::success('deleted');
    }
}
