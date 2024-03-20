<?php

namespace App\Http\Controllers;

use App\Models\Policy;
use Illuminate\Http\Request;
use App\Helper\ResponseHelper;
use App\Services\PolicyServices;
use App\Http\Requests\PolicyRequest\StorePolicyRequest;

class PolicyController extends Controller
{
    protected $PolicyServices;

    public function __construct(PolicyServices $PolicyServices)
    {
        $this->PolicyServices = $PolicyServices;
    }

    /**
     * Show policies of a branch & it's rate types.
     * [PolicyServices => BranchPolicy]
     * @param Request
     * @return Policy
     */
    public function show(Request $request)
    {
        try {
            return $this->PolicyServices->BranchPolicy($request);
             $policy;
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Store policy & rate types of a branch .
     * [PolicyServices => StorePolicy]
     * @param Request
     * @return Policy
     */
    public function store(StorePolicyRequest $request)
    {
        try {
            $policy = $this->PolicyServices->StorePolicy($request);
            return $policy;
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Update policy & rate types of a branch .
     * [PolicyServices => UpdatePolicy]
     * @param Request
     * @return Policy
     */
    public function update(Request $request)
    {
        try {
           $policy =  $this->PolicyServices->UpdatePolicy($request);
           return $policy;
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Delete policy & rate types of a branch .
     * [PolicyServices => DeletePolicy]
     * @param Request
     * @return Policy
     */
    public function destroy(Request $request)
    {
        try {
            $policy = $this->PolicyServices->DeletePolicy($request);
            return $policy;
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), $e->getCode());
        }
    }
}
