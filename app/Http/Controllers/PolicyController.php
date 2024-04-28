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
     *@param Request
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function show(Request $request)
    {
            $policy = $this->PolicyServices->BranchPolicy($request);
            return $policy;
        
    }

    /**
     * Store policy & rate types of a branch .
     * [PolicyServices => StorePolicy]
     * @param Request
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {

            $policy = $this->PolicyServices->StorePolicy($request);
            return $policy;

    }

    /**
     * Update policy & rate types of a branch .
     * [PolicyServices => UpdatePolicy]
     * @param Request
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
            $policy =  $this->PolicyServices->UpdatePolicy($request);
            return $policy;

    }

    /**
     * Delete policy & rate types of a branch .
     * [PolicyServices => DeletePolicy]
     * @param Request
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request)
    {

            $policy = $this->PolicyServices->DeletePolicy($request);
            return $policy;

    }
}
