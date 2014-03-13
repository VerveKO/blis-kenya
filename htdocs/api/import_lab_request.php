<?php
/**
 * Handles External Lab Requests from External Systems i.e. HMIS/EMR systems
 * 
 * Perfoms the following imports:
 * 	1. 	Handles JSON POST data from sanitas labrequest Outbound URL and passes 
 * 		it to the API method save_external_lab_request and INSERTS it to the database
 * 		table => external_lab_request
 * 		(Sanitas->Administration-> Integration->BLISS)
 * 
 * 	2. 	Queries the view LabRequestQueryForBliss from Microsoft SQL Server and INSERTS it to the database
 * 		table => external_lab_request
 */
if(session_id() == "")session_start();
$_SESSION['SESS_TIMER'] = time();

require_once("../includes/db_lib.php");
$time_stamp = date("Y-m-d H:i:s");
$error_log_path ="../logs/blis.api.error.log";

$value_string = '';
$length = count($_POST);
function age($date){
	$year_diff = '';
	$time = strtotime($date);
		
	if(FALSE === $time){
		return '';
	}
		
	$date = date('Y-m-d', $time);
	list($year,$month,$day) = explode("-",$date);
	$year_diff = date("Y") - $year;
	$month_diff = date("m") - $month;
	$day_diff = date("d") - $day;
	if ($day_diff < 0 || $month_diff < 0) $year_diff–;
		
	return $year_diff;
}
if (!$length >1 || !$_POST==null){
	foreach($_POST as $key=>$value)
	{
		if ($key='labRequest'){
			
		 	$value_string = '';
		 	
		 	$json_request = (string)$value;
		 	error_log("\n".$time_stamp.": Lab Request Recieved: ======", 3, $error_log_path);
		 	$request_data = json_decode($json_request, true);

		 	$value_string.= '(';
		 	$value_string.= 
		 	#labNo
		 	'"'.$request_data['labNo'].'",'.
		 	#parentLabNo
		 	'"'.$request_data['parentLabNo'].'",'.
		 	#requestingClinician
		 	'"'.$request_data['requestingClinician'].'",'.
		 	#investigation
		 	'"'.$request_data['investigation'].'",'.
		 	#requestDate
		 	'"'.$request_data['requestDate'].'",'.
		 	#orderStage
		 	'"'.$request_data['orderStage'].'",'.
		 	#patientVisitNumber
		 	'"'.$request_data['patientVisitNumber'].'",'.
		 	#patient_id
		 	'"'.$request_data['patient']['id'].'",'.
		 	#full_name
		 	'"'.$request_data['patient']["fullName"].'",'.
		 	#dateOfBirth
		 	'"'.$request_data['patient']["dateOfBirth"].'",'.
		 	#age
		 	'"'."NULL".'",'.
		 	#gender
		 	'"'.$request_data['patient']['gender'].'",'.
		 	#address
		 	'"'.$request_data['address']["address"].'",'.
		 	#postalCode
		 	'"'.$request_data['address']["postalCode"].'",'.
		 	#phoneNumber
		 	'"'.$request_data['address']["phoneNumber"].'",'.
		 	#city
		 	'"'.$request_data['address']["city"].'",'.
		 	#revisitNumber
		 	'"'."NULL".'",'.
		 	#cost
		 	'"'.$request_data['cost'].'",'.
		 	#patientContact
		 	'"'."NULL".'",'.
		 	#receiptNumber
		 	'"'.$request_data['receiptNumber'].'",'.
		 	#receiptType
		 	'"'.$request_data['receiptType'].'",'.
		 	#waiverNo
		 	'"'."NULL".'",'.
		 	#comments
		 	'"'."NULL".'",'.
		 	#provisionalDiagnosis
		 	'"'."NULL".'",'.
		 	#system_id
		 	'"'."sanitas".'"';
		 	
		 	$value_string.= ')';
		 	
		 	$LabRequest = $value_string;
		 	
		 	//Save all requests except waivers and exemptions
		 	if ($request_data['receiptType'] != 'exemption' && $request_data['receiptType'] !='waiver')
		 	{
		 			API::save_external_lab_request($LabRequest);
		 		
		 	}
		 } 		 
		}
}
?>
