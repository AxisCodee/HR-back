<?php

namespace App\Http\Controllers;

use App\Helper\ResponseHelper;
use Illuminate\Http\Request;
use App\Models\Decision;
use App\Models\User;
use App\Http\Requests\DecisionRequest;
use App\Models\Absences;
use App\Models\Late;
use App\Models\UserInfo;
use Illuminate\Support\Facades\Auth;

class DecisionController extends Controller
{
//add new decision for a user
    public function new_decision(DecisionRequest $request)
    {
        $new = $request->validated();
        $created = Decision::create($new);
        return ResponseHelper::created($created,'decision created successfully');
    }
//delete an exisiting decision
    public function remove_decision($id)
    {
        $removed = Decision::findOrFail($id)
                            ->delete();
        return ResponseHelper::deleted(' decision deleted successfully');
    }
//edit an exisiting decision
    public function edit_decision(DecisionRequest $request,$id)
    {
        $validate = $request->validated();
        $edited = Decision::findOrFail($id)->with('user_decision');
        $edited->update($validate);
        return ResponseHelper::updated($edited,'decision updated successfully');
    }
//get all decisions for all users
    public function all_decisions()
    {
        $all = Decision::query()
                        ->with('user_decision')
                        ->get();
        return ResponseHelper::success($all, null, 'all decisions returned successfully', 200);
    }
//get decisions for the current user
    public function my_decisions()
    {
        $mine = Decision::query()
                        ->where('user_id',Auth::id())
                        ->get();
        return ResponseHelper::success($mine, null, 'user decisions returned successfully', 200);
    }


    public function DynamicDecision($Absences)
    {

     $salary= UserInfo::query()->where('user_id',$Absences->user_id)->value('salary');
           $salaryInHour=$salary/208;
           $deduction= $salaryInHour*8;
            Decision::query()->createOrUpdate(
                [
                    'user_id'=>$Absences->user_id,
                    'type'=>'warning',
                    'salary'=>$salary,
                    'dateTime'=>$Absences->startDate,
                    'fromSystem'=>true,
                    'content'=>'Unjustified absence',
                    'ammount'=> $deduction
                ]
                );

        $lates=Late::query()->where('type','Unjustified')->get();
        foreach($lates as $late)
{
    $salary= UserInfo::query()->where('user_id',$late->user_id)->value('salary');
    $salaryInHour=$salary/208;
    $numberOfHour=$late->hours_num;
    $deduction= $salaryInHour*$numberOfHour;

    Decision::query()->createOrUpdate(
        [
            'user_id'=>$late->user_id,
            'type'=>'warning',
            'salary'=>$salary,
            'dateTime'=>$late->startDate,
            'fromSystem'=>true,
            'content'=>'Unjustified late',
            'ammount'=> $deduction
        ]
        );

}


    }
}
