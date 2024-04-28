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
use Illuminate\Http\Request;
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
    public function __construct(Request $request,FingerprintService $fingerprintService)
    {
        $this->request = $request;
        $this->fingerprintService = $fingerprintService;

    }




    /**
     * Execute the job.
     */
   /**
 * Execute the job.
 */
public function handle($request)
{
        //Storing attendance
        $branch = Branch::findOrFail($request->branch_id);
        dd($branch);
        $tad_factory = new TADFactory(['ip' => $branch->fingerprint_scanner_ip]);
        $tad = $tad_factory->get_instance();
        // $all_user_info = $tad->get_all_user_info();
        // $dt = $tad->get_date();
        $logs = $tad->get_att_log();
        //check date table and store attendance
        $uniqueDates = [];
        if (Date::all()->count() != 0) {
            $start = Date::latest('date')->value('date');
            $end = Carbon::now()->format('Y-m-d');
            $filtered_att_logs = $logs->filter_by_date(
                ['start' => $start, 'end' => $end]
            );
            $xml = simplexml_load_string($filtered_att_logs);
            $uniqueDates = $this->fingerprintService->convertAndStoreAttendance($xml);
            $allAttendances = Attendance::query()
                ->whereRaw('DATE(datetime) BETWEEN ? AND ?', [$start, $end])
                ->get();
        } elseif (Date::all()->count() == 0) {
            $xml = simplexml_load_string($logs);
            $uniqueDates = $this->fingerprintService->convertAndStoreAttendance($xml);
            $allAttendances = Attendance::query()->get();
        }
        //Storing delays
        foreach ($allAttendances as $attendance) {
            $this->fingerprintService->storeUserDelays($attendance->pin, $request->branch_id, $attendance->datetime, '0');
            $this->fingerprintService->storeUserDelays($attendance->pin, $request->branch_id, $attendance->datetime, '1');
        }
        //Storing absence
        foreach ($uniqueDates as $date) {
            $this->fingerprintService->clearDelays($request->branch_id, $date);
            $this->fingerprintService->storeUserAbsences($date, $request->branch_id);
        }
        return ResponseHelper::success([], null, 'Attendances logs stored successfully');


}
}
