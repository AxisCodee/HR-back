<?php

namespace App\Services;

use App\Models\Late;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Helper\ResponseHelper;
use function Symfony\Component\String\s;
use Illuminate\Support\Facades\DB;

class LateService
{
    public function lateTypes($request)
    {
        $user = User::with(
            'UnPaidLates',
            'PaidLates',
            'sickLates'
        )->findOrFail($request->user_id);

        $paidLates = $user->PaidLates;
        $unpaidLates = $user->UnPaidLates;
        $sickLates = $user->sickLates;

        return ['Paid' => $paidLates,
            'UnPaid' => $unpaidLates,
            'Sick' => $sickLates];
    }

    public function allUserLates(Request $request)
    {
        $user = User::with('allLates')->with('UnPaidLates')->with('PaidLates')->findOrFail($request->user_id);
        return $user;
    }




    // public static function userLates(Request $request)
    // {
    //     $result = User::query()
    //         ->with('userInfo:id,image', 'department', 'UnPaidLates', 'PaidLates', 'sickLates')
    //         ->get(['id', 'first_name','middle_name','last_name'])->toArray();
    //     return $result;
    // }

    public static function userLates(Request $request)
    {
        $result = User::query()->where('branch_id', $request->branch_id)
            ->with('userInfo:id,image', 'department', 'UnPaidLates', 'PaidLates', 'sickLates')
            ->withCount([
                'justifiedPaidLatesCount as justifiedPaid' => function ($query) {
                    $query->select(DB::raw("SUM(hours_num)"));
                },
                'justifiedUnPaidLatesCount as justifiedUnpaid' => function ($query) {
                    $query->select(DB::raw("SUM(hours_num)"));
                },
                'UnjustifiedPaidLatesCount as UnjustifiedPaid' => function ($query) {
                    $query->select(DB::raw("SUM(hours_num)"));
                },
                'UnjustifiedUnPaidLatesCount as UnjustifiedUnpaid' => function ($query) {
                    $query->select(DB::raw("SUM(hours_num)"));
                },
            ])
            ->get()
            ->toArray();

        return $result;

    }

    public function editLate($request)
    {
        Late::query()->where('id', $request->id)
            ->update([
                'type' => $request->type,
                'isPaid' => $request->isPaid
            ]);
        return true;

    }
}
