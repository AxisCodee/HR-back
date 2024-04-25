<?php

namespace App\Jobs;

use App\Helper\ResponseHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Branch;
use App\Models\Attendance;
use App\Models\Date;
use App\Models\User;
use App\Models\Late;
use App\Models\Policy;
use App\Models\Absences;
use App\Services\FingerprintService;
use Carbon\Carbon;
use TADPHP\TADFactory;

require 'tad\vendor\autoload.php';

class StoreAttendanceLogsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public $branch_id,$request;
    public $fingerprintService;
    public function __construct($branch_id,FingerprintService $fingerprintService)
    {
        $this->branch_id = $branch_id;
        $this->fingerprintService = $fingerprintService;

    }




    /**
     * Execute the job.
     */
    public function handle()
    {
             DB::transaction(function () {
            $branch = Branch::findOrFail($this->branch_id);
            $tad_factory = new TADFactory(['ip' => $branch->fingerprint_scanner_ip]);
            $tad = $tad_factory->get_instance();
            $logs = $tad->get_att_log();

            $xml = simplexml_load_string($logs);
            $array = json_decode(json_encode($xml), true);
            $logsData = $array['Row'];
            $uniqueDates = [];

            foreach ($logsData as $log) {
                $this->fingerprintService->storeAttendance($log);

                $date = date('Y-m-d', strtotime($log['DateTime']));
                Date::updateOrCreate(['date' => $date]);

                $checkInDate = substr($log['DateTime'], 0, 10);
                if (!in_array($checkInDate, $uniqueDates)) {
                    $uniqueDates[] = $checkInDate;
                }
            }

            $allAttendances = Attendance::query()->get();
            foreach ($allAttendances as $attendance) {
                $this->fingerprintService->storeUserDelays($attendance->pin, $this->branch_id, $attendance->datetime, '0');
                $this->fingerprintService->storeUserDelays($attendance->pin, $this->branch_id, $attendance->datetime, '1');
            }

            foreach ($uniqueDates as $date) {
                $this->fingerprintService->clearDelays($this->branch_id, $date);
                $this->fingerprintService->storeUserAbsences($date, $this->branch_id);
            }

            return ResponseHelper::success([], null, 'attendance logs stored successfully', 200);
        });

}}
