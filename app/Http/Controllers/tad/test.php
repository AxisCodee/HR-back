<?php
// Include the Composer's autoloader



// require 'tad/lib/TADFactory.php';
// require 'tad/lib/TAD.php';
// require 'tad/lib/TADResponse.php';
// require 'tad/lib/Providers/TADSoap.php';
// require 'tad/lib/Providers/TADZKLib.php';
// require 'tad/lib/Exceptions/ConnectionError.php';
// require 'tad/lib/Exceptions/FilterArgumentError.php';
// require 'tad/lib/Exceptions/UnrecognizedArgument.php';
// require 'tad/lib/Exceptions/UnrecognizedCommand.php';



// Instantiate the TADFactory class
//$factory = new TADFactory();


// Get the first TAD device
//$device = $factory->getDevice('192.168.2.202', '4370'); // replace with your device IP and port

// Get the attendance log
//$attendanceLog = $device->getAttendanceLog();

// Print the attendance log
//print_r($attendanceLog);

use TADPHP\TAD;
use TADPHP\TADFactory;
require 'vendor/autoload.php';

$tad_factory = new TADFactory(['ip'=>'192.168.2.202']);
$tad = $tad_factory->get_instance();

$all_user_info = $tad->get_all_user_info();
$dt = $tad->get_date();
$logs = $tad->get_att_log();
//header('Content-Type: application/json');
//$result=json_encode($logs);
$xml = simplexml_load_string($logs);
$array = json_decode(json_encode($xml));
$json = json_encode($array);




$json_data = $logs->get_response(['format' => 'json']);
$decoded_json = json_decode(stripslashes($json_data), true);
$json_output = json_encode($decoded_json);
echo $json_output;

// $json_data = $logs->get_response(['format' => 'json']);
// $decoded_json = json_decode(stripslashes($json_data), true);
// $json_output = json_encode($decoded_json);
// echo $json_output;


?>
