<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Report;
use App\Helper\ResponseHelper;
use Illuminate\Support\Facades\Http;
use App\Http\Requests\ReportRequest;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
//store new Report
    public function store(ReportRequest $request)
    {
        $validate = $request->validated();
        $new_report = Report::create([

            'user_id'=> Auth::user()->id,
            'content'=> $request->content,
        ]);
        return ResponseHelper::success($new_report, null, 'report created successfully', 200);
    }
//remove existing report by a specific user
    public function remove($id)
    {
        $remove = Report::findorFail($id)->delete();
        return ResponseHelper::success($remove, null, 'report removed successfully', 200);
    }
//get all user's reports
    public function all_reports()
    {
        $all = Report::query()->where('user_id',Auth::user()->id)->get();
        return ResponseHelper::success($all, null, 'all user reports returned successfully', 200);
    }

}
