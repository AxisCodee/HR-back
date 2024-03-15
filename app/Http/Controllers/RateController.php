<?php

namespace App\Http\Controllers;

use App\Models\Rate;
use App\Models\User;
use App\Http\Requests\RateRequest\StoreRateRequest;
use App\Http\Requests\RateRequest\UpdateRateRequest;
use App\Helper\ResponseHelper;
use App\Models\RateType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use App\Services\RateService;
use Illuminate\Http\Request;

class RateController extends Controller
{
    protected $rateService;

    /**
     * Define the constructor to use the service.
     * @param RateService
     * @return none
     */
    public function __construct(RateService $rateService)
    {
        $this->rateService = $rateService;
    }

    /**
     * Get the rates of a user.
     * [RateService => UserRates]
     * @param RateService
     * @return none
     */
    public function index(User $user)
    {
        try {
            return  $this->rateService->UserRates($user);
        } catch (\Exception $e) {
            return ResponseHelper::error($e, null, 'error', 403);
        }
    }

    /**
     * Rate a user with a rate type.
     * [RateService => setRate]
     * @param StoreRateRequest
     * @return none
     */
    public function setRate(StoreRateRequest $request)
    {
        $userId = $request->user_id;
        $rateTypeId = $request->rate_type_id;
        $rate = $request->rate;
        try {
            $result = $this
                ->rateService
                ->setRate($userId, $rateTypeId, $rate);
            return ResponseHelper::success($result, null, 'Rate added successfully', 200);
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 422);
        }
    }

    /**
     * Update a user's rate.
     * [RateService => UpdateRate]
     * @param UpdateRateRequest
     * @param Rate
     * @return none
     */
    public function update(UpdateRateRequest $request, Rate $rate)
    {
        try {
            return  $this->rateService->UpdateRate($request, $rate);
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 422);
        }
    }

    /**
     * Update a user's rate.
     * [RateService => Delete]
     * @param Rate
     * @return none
     */
    public function destroy(Rate $rate)
    {
        try {
            return $this->rateService->Delete($rate);
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 422);
        }
    }

    /**
     * Show the rate of the authenticated user.
     * [RateService => MyRate]
     * @param User
     * @return none
     */
    public function showMyRate(User $user)
    {
        try {
            return  $this->rateService->MyRate($user);
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 422);
        }
    }

    /**
     * Get rates of a user.
     * [RateService => getRate]
     * @param Request
     * @param User
     * @return none
     */
    public function getRate(Request $request, $id)
    {
        try {
            return $this->rateService->getRate($request, $id);
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 422);
        }
    }

    /**
     * Get rates of all users.
     * [RateService => allRates]
     * @param Request
     * @return none
     */
    public function allRates(Request $request)
    {
        try {
            return $this->rateService->allRates($request);
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 422);
        }
    }

    /**
     * Get rates of a user in a date.
     * [RateService => DateRate]
     * @param Request
     * @param $date
     * @return none
     */
    public function userRates(Request $request, $date)
    {
        try {
            return $this->rateService->DateRate($request, $date);
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Get rate type of a user in a date.
     * [RateService => UserRateType]
     * @param Request
     * @param $date
     * @return none
     */
    public function userRate(Request $request, $date)
    {
        try {
            return $this->rateService->UserRateType($request, $date);
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), $e->getCode());
        }
    }
}
