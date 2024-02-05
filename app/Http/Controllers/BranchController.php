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
        $branches = Branch::paginate(10)->toArray();
        return ResponseHelper::success($branches, null);
    }
    public function store(Request $request)
    {
        $result = Branch::query()->create([
            'name' => $request->name
        ]);
        return ResponseHelper::success($result, null);
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
