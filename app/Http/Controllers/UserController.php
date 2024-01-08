<?php

namespace App\Http\Controllers;

use App\Helper\ResponseHelper;
use Illuminate\Http\Request;
use App\Models\User;
use TADPHP\TAD;
use TADPHP\TADFactory;
require 'tad\vendor\autoload.php';

class UserController extends Controller
{

//get all users info
    public function all_users()
    {
        $tad_factory = new TADFactory(['ip'=>'192.168.2.202']);
        $tad = $tad_factory->get_instance();
        $logs = $tad->get_all_user_info();
        $xml = simplexml_load_string($logs);
        $array = json_decode(json_encode($xml));
        $json = json_encode($array);
        $json_data = $logs->get_response(['format' => 'json']);
        $decoded_json = json_decode(stripslashes($json_data), true);
        $json_output = json_encode($decoded_json);
    //to return the response as a normal JSON response we should decode it again
        $json_output = json_decode($json_output, true);
        return ResponseHelper::success($json_output, null, 'success', 200);
    }
//get a specific user by the ID
    public function specific_user($id)
    {
        $spec_user = User::findOrFail($id);

    }
}
