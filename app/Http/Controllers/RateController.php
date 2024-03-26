<?php

namespace App\Http\Controllers;

use App\Models\Rate;
use App\Models\User;
use App\Http\Requests\RateRequest\StoreRateRequest;
use App\Http\Requests\RateRequest\UpdateRateRequest;
use App\Helper\ResponseHelper;
use App\Services\RateService;
use Illuminate\Http\Request;

class RateController extends Controller
{
    protected $rateService;

    /**
     * Define the constructor to use the service.
     * @param RateService
     */
    public function __construct(RateService $rateService)
    {
        $this->rateService = $rateService;
    }

    /**
     * Get the rates of a user.
     * [RateService => UserRates]
     * @param RateService
     */
    public function index(User $user)
    {
        try {
            return $this->rateService->UserRates($user);
        } catch (\Exception $e) {
            return ResponseHelper::error($e, null, 'error', 403);
        }
    }

    /**
     * Rate a user with a rate type.
     * [RateService => setRate]
     * @param StoreRateRequest
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
     */
    public function update(UpdateRateRequest $request, Rate $rate)
    {
        try {
            return $this->rateService->UpdateRate($request, $rate);
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 422);
        }
    }

    /**
     * Update a user's rate.
     * [RateService => Delete]
     * @param Rate
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
     */
    public function showMyRate(User $user)
    {
        try {
            return $this->rateService->MyRate($user);
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 422);
        }
    }

    /**
     * Get rates of a user.
     * [RateService => getRate]
     * @param Request
     * @param User
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
     * Get rate type of user in a date.
     * [RateService => UserRateType]
     * @param Request
     * @param $date
     */
    public function userRate(Request $request, $date)
    {
        try {
            return $this->rateService->UserRateType($request, $date);
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), $e->getCode());
        }
    }
    public function review()
    {
        try {
           $result= $this->rateService->reviews();
           return ResponseHelper::success($result, null);
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), $e->getCode());
        }
    }
    public function reviewDetails($ratId)
    {
        try {
           $result= $this->rateService->reviewDetails($ratId);
           return ResponseHelper::success($result, null);
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), $e->getCode());
        }
    }
    public function userReview(Request $Request)
    {
        try {
           $result= $this->rateService->getUserReview($Request->userId,$Request->rateId);
           return ResponseHelper::success($result, null);
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), $e->getCode());
        }
    }
    public function updateReview(Request $Request, Rate $rate)
    {
        try {
           $result= $this->rateService->update($Request,$rate);
           return ResponseHelper::success($result, null);
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), $e->getCode());
        }
    }
    public function reportReview(User $user)
    {
        try {
           $result= $this->rateService->ReportReview($user);
           return ResponseHelper::success($result, null);
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), $e->getCode());
        }
    }
}
