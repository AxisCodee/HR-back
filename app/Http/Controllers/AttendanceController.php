<?php
namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;
use TADPHP\TAD;
use TADPHP\TADFactory;
require 'tad/vendor/autoload.php';

class AttendanceController extends Controller
{
    public function getAttendanceLogs()
    {
        $tad_factory = new TADFactory(['ip' => '192.168.2.202']);
        $tad = $tad_factory->get_instance();

        $all_user_info = $tad->get_all_user_info();
        $dt = $tad->get_date();
        $logs = $tad->get_att_log();

        $xml = simplexml_load_string($logs);
        $array = json_decode(json_encode($xml));
        $json_output = json_encode($array);

    echo $json_output;
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
            Attendance::create([
                'pin' => $log['PIN'],
                'datetime' => $log['DateTime'],
                'verified' => $log['Verified'],
                'status' => $log['Status'],
                'work_code' => $log['WorkCode']
            ]);
        }
    }


    //_____________________add new normal user__________________//
// $tad_factory = new TADFactory(['ip'=>'192.168.2.202']);
// $tad = $tad_factory->get_instance();
// $r = $tad->set_user_info([
//     'pin' => USER_PIN2,//this is the pin2 in the returned response
//     'name'=> 'testing user',
//     'privilege'=> 0,//if you want to add a superadmin user make the privilege as '14'.
//     'password' => ''
// ]);
}

