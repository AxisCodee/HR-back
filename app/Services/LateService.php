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
        $user = User::with(['PaidLates' => function ($builder) {
            return $builder->latest('lateDate');
        },'UnPaidLates' => function ($builder) {
            return $builder->latest('lateDate');
        }])->findOrFail($request->user_id);
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
            ->with('userInfo:id,image,user_id', 'department', 'UnPaidLates', 'PaidLates', 'sickLates')
            ->withCount([
                'justifiedPaidLatesCount as justifiedPaid' => function ($query) {
                    $query->select(DB::raw("FLOOR(SUM(FLOOR(hours_num) * 60 + (hours_num - FLOOR(hours_num)) * 100) / 60) +
            (SUM(FLOOR(hours_num) * 60 + (hours_num - FLOOR(hours_num)) * 100) % 60) / 100"));
                },
                'justifiedUnPaidLatesCount as justifiedUnpaid' => function ($query) {
                    $query->select(DB::raw("FLOOR(SUM(FLOOR(hours_num) * 60 + (hours_num - FLOOR(hours_num)) * 100) / 60) +
            (SUM(FLOOR(hours_num) * 60 + (hours_num - FLOOR(hours_num)) * 100) % 60) / 100"));
                },
                'UnjustifiedPaidLatesCount as UnjustifiedPaid' => function ($query) {
                    $query->select(DB::raw("FLOOR(SUM(FLOOR(hours_num) * 60 + (hours_num - FLOOR(hours_num)) * 100) / 60) +
            (SUM(FLOOR(hours_num) * 60 + (hours_num - FLOOR(hours_num)) * 100) % 60) / 100"));
                },
                'UnjustifiedUnPaidLatesCount as UnjustifiedUnpaid' => function ($query) {
                    $query->select(DB::raw("FLOOR(SUM(FLOOR(hours_num) * 60 + (hours_num - FLOOR(hours_num)) * 100) / 60) +
            (SUM(FLOOR(hours_num) * 60 + (hours_num - FLOOR(hours_num)) * 100) % 60) / 100"));
                },
            ])
            ->whereNot('role', 'admin')
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
