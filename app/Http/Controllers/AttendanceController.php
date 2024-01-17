<?php
namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\Request;
use TADPHP\TAD;
use TADPHP\TADFactory;
use App\Helper\ResponseHelper;
use Illuminate\Support\Facades\Http;
require 'tad\vendor\autoload.php';

class AttendanceController extends Controller
{
    public function getAttendanceLogs()
    {
        $tad_factory = new TADFactory(['ip' => '192.168.2.202']);
        $tad = $tad_factory->get_instance();


        $logs = $tad->get_att_log();

        $xml = simplexml_load_string($logs);
        $array = json_decode(json_encode($xml));
        return ResponseHelper::success($array, null, 'all logs returned successfully', 200);
    }

    public function employees_percent()
    {   $all_users = User::query()->count();
        $attended_users = Attendance::whereDate('datetime', now()->format('Y-m-d'))->where('status','0')->count();
        return ResponseHelper::success(
            ['present_employees'=> $attended_users,
            'total_employees' =>$all_users]
            , null, 'attended users returned successfully', 200);
    }


    public function storeAttendanceLogs()
    {
        $tad_factory = new TADFactory(['ip' => '192.168.2.202']);
        $tad = $tad_factory->get_instance();

        $all_user_info = $tad->get_all_user_info();
        $dt = $tad->get_date();
        $logs = $tad->get_att_log();

        $xml = simplexml_load_string($logs);
        $array = json_decode(json_encode($xml), true);
        $logsData = $array['Row'];
        foreach ($logsData as $log) {
            $attendance = [
                'pin' => $log['PIN'],
                'datetime' => $log['DateTime'],
                'verified' => $log['Verified'],
                'status' => $log['Status'],
                'work_code' => $log['WorkCode']
            ];

            $checkInDate = substr($log['DateTime'], 0, 10);
            $pendingCheckIn = Attendance::where('pin', $log['PIN'])
        ->where('datetime', 'LIKE', $checkInDate . '%')
        ->where('status', 0)
        ->first();
        if($pendingCheckIn)
        {

        }
        else
        {

            Attendance::updateOrCreate(['datetime' => $log['DateTime']], $attendance);

        }
    }
        return ResponseHelper::success([], null, 'attendaces logs stored successfully', 200);
    }

    public function showAttendanceLogs(){

        $result=User::with('department')->with('attendance')->get();

        return  ResponseHelper::success([
            $result
        ]);
    }

}
