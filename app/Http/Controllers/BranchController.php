<?php

namespace App\Http\Controllers;

use App\Helper\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function addBranche(Request $request) {
        $result=Branch::query()->create([

            'name'=>$request->name
        ]);
        return ResponseHelper::success('branche added successfully');
    }
}
