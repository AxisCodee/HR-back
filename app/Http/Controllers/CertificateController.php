<?php

namespace App\Http\Controllers;

use App\Helper\ResponseHelper;
use App\Http\Requests\CertificateRequest\StoreCertificateRequest;
use App\Http\Requests\CertificateRequest\UpdateCertificateRequest;
use App\Models\Certificate;
use Illuminate\Support\Facades\DB;
use Mockery\Exception;

class CertificateController extends Controller
{
    public function store(StoreCertificateRequest $request)
    {
        $validate = $request->validated();
        return DB::transaction(function () use ($validate) {
            $cer = Certificate::query()->create($validate);
            return ResponseHelper::success($cer, null);
        });
    }
    public function update(UpdateCertificateRequest $request, $id)
    {
        $validate = $request->validated();
        return DB::transaction(function () use ($validate, $id) {
            Certificate::query()
                ->findOrFail($id)
                ->update($validate);
            return ResponseHelper::success('Certificate has been updated', null);
        });
    }

    public function destroy($id)
    {
        return DB::transaction(function () use ($id) {
            $cer = Certificate::query()->findOrFail($id);
            $cer->delete();
            return ResponseHelper::success('Certificate has been deleted', null);
        });
    }
}
