<?php
namespace App\Http\Controllers;

use App\Models\Attendance;

use App\Models\User;
use App\Models\Absences;
use App\Models\Date;
use Illuminate\Http\Request;
use TADPHP\TAD;
use TADPHP\TADFactory;
use App\Helper\ResponseHelper;
use App\Models\DatePin;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
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
        $uniqueDates = [];
        foreach ($logsData as $log) {

            $attendance = [
                'pin' => $log['PIN'],
                'datetime' => $log['DateTime'],
                'verified' => $log['Verified'],
                'status' => $log['Status'],
                'work_code' => $log['WorkCode']
            ];

            Attendance::updateOrCreate(['datetime' => $log['DateTime']], $attendance);
            $checkInDate = substr($log['DateTime'], 0, 10);
            if (!in_array($checkInDate, $uniqueDates)) {
                $uniqueDates[] = $checkInDate;
            }
        }


foreach($uniqueDates as $date)
{
            $today = Carbon::now()->format('y-m-d');
            if (!Carbon::parse($today)->equalTo(Carbon::parse($date))) {
                // if (!in_array($checkInDate, $uniqueDates)) {
                //     $uniqueDates[] = $checkInDate;

                $usersWithoutAttendance = DB::table('users')
                ->leftJoin('attendances', function ($join) use($date){
                    $join->on('users.pin', '=', 'attendances.pin')
                  ->whereRaw('DATE(attendances.datetime) = ?', $date);

                })
                 ->whereNull('attendances.pin')
                ->select('users.*')
                ->get();
               // dd($usersWithoutAttendance);
                if (!empty($usersWithoutAttendance)) {
                    foreach ($usersWithoutAttendance as $user) {

                        $absence = DB::table('absences')
                        ->where('user_id',$user->id)
                        ->whereRaw('? BETWEEN startDate AND endDate',$date)
                            ->first();

                        if (!$absence) {
                            Absences::updateOrCreate([
                                'user_id' => $user->id,
                                'startDate' =>$date,

                            ]);
                        }
                    }
            }
        }
    }


    //         $checkInDate = substr($log['DateTime'], 0, 10);
    //         $pendingCheckIn = Attendance::where('pin', $log['PIN'])
    //         ->where('datetime', 'LIKE', $checkInDate . '%')
    //         ->where(function ($query) {
    //             $query->where('status', 0)
    //                 ->orWhere('status', 1);
    //         })
    //         ->get();

    //     if($pendingCheckIn)
    //     {

    //     }
    //     else
    //     {

    //  $attendence=Attendance::updateOrCreate(['datetime' => $log['DateTime']], $attendance);
    // $date= Date::updateOrCreate(
    //             ['date'=> $checkInDate]
    //         );
    //         DatePin::updateOrCreate(
    //             [
    //             'pin'=>$attendence->pin,
    //             'date_id'=>$date->id
    //             ]
    //             );




    //     }

    //         if ($pendingCheckIn) {
    //         } else {
    //             Attendance::updateOrCreate(['datetime' => $log['DateTime'],'status'=>$log['Status']], $attendance);
    //         }


        return ResponseHelper::success([], null, 'attendaces logs stored successfully', 200);


    }

    public function showAttendanceLogs()
    {

        $result=User::with('department')->with('attendance')->get()->toArray();

        return  ResponseHelper::success(
            $result
        );
    }

    public function DayAttendance($date)
    {
        $users = User::with('department')->with(['attendance' => function ($query) use ($date) {
            $query->whereDate('datetime', $date);
        }])
        ->has('attendance')
        ->get();

        return ResponseHelper::success($users);
    }

    public function showAttendanceUser($user)
    {
        $result = User::with('attendance')
        ->where('id', $user)
        ->get()->toArray();

        return ResponseHelper::success($result);
    }

}
