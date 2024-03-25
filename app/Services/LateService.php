<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Helper\ResponseHelper;
use function Symfony\Component\String\s;

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
        $user = User::with('allLates')->findOrFail($request->user_id);
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
        $result = User::query()
            ->with('userInfo:id,image', 'department', 'UnPaidLates', 'PaidLates', 'sickLates')
            ->with('justifiedPaidLatesCount '
                , 'justifiedUnPaidLatesCount '
                , 'UnjustifiedPaidLatesCount '
                , 'UnjustifiedUnPaidLatesCount ')
            ->get()
            ->toArray();

        return $result;
    }


}
