<?php

namespace App\Http\Controllers;

use App\Helper\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\BranchRequest;
use App\Services\BranchService;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    protected $BranchService;

    /**
     * Define the constructor to use the branch services.
     */
    public function __construct(BranchService $BranchService)
    {
        $this->BranchService = $BranchService;
    }

    /**
     * Get all existing branches using the service.
     */
    public function index()
    {
        try {
            $branches = $this->BranchService->index();
        } catch (\Throwable $th) {
            return ResponseHelper::error($th, null);
        }
        return ResponseHelper::success($branches, null);
    }

    /**
     * Create a new branch and store it using the service.@
     */
    public function store(BranchRequest $request)
    {
        try {
            $newbranch = $this->BranchService->store($request);
            return $newbranch;
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHelper::error($e->validator->errors()->first(), 400);
        } catch (\Illuminate\Database\QueryException $e) {
            return ResponseHelper::error('Invalid branch name or IP', 400);
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Get a specific branch by id.
     */
    public function show($id)
    {
        try {
            $branch = $this->BranchService->show($id);
            return ResponseHelper::success($branch, null);
        } catch (\Throwable $th) {
            return ResponseHelper::error($th);
        }
    }

    /**
     * Update a specific branch by id.
     */
    public function update(Request $request, $id)
    {
        try {
            $updated = $this->BranchService->update($request, $id);
            return $updated;
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHelper::error($e->validator->errors()->first(), 400);
        } catch (\Illuminate\Database\QueryException $e) {
            return ResponseHelper::error('Invalid branch name or IP', 400);
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Delete a specific branch by id.
     */
    public function destroy(Request $request, $id)
    {
        try {
            $remove = $this->BranchService->destroy($request, $id);
            return $remove;
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), $e->getCode());
        }
    }
}
