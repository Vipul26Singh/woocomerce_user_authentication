<?php


include_once '../../../../wp-load.php';

function generateOtp($count){
	$otp ="";
	for($indx=0; $indx<$count; $indx++){
		$otp.=mt_rand(0, 9);
	}
	return $otp;
}


function send_bulksms($mobile, $message){
	$user = "minbazar";
	$key = "5a11203f26XX";
	$usage = "1";
	$sender_id = "MINBZR";
	$xml_data ='<?xml version="1.0"?>
		<parent>
		<child>
		<user>'.$user.'</user>
		<key>'.$key.'</key>
		<mobile>'.$mobile.'</mobile>
		<message>'.$message.'</message>
		<accusage>'.$usage.'</accusage>
		<senderid>'.$sender_id.'</senderid>
		</child>
		</parent>';

	$URL = "sms.bulkssms.com/submitsms.jsp?"; 

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $URL); 
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_ENCODING, 'UTF-8');
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/xml'));
	curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_data);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$output = curl_exec($ch);
	curl_close($ch);

	return (explode(',', $output)); 
}

function insertInTable($mobile, $otp, $error_message = null){
	global $wpdb;

	$insertData = array(
			'mobile_number'  => $mobile, 
			'otp_value'  => $otp, 
			'transaction_date'   => date("Y-m-d H:i:s"), 
			'error_message' => $error_message
			);
	$wpdb->insert( $wpdb->prefix."minbazaar_otp", $insertData);
}

function findOtp($mobile){
	global $wpdb;
	$otp_val = $wpdb->get_var( $wpdb->prepare("SELECT otp_value FROM ".$wpdb->prefix."minbazaar_otp WHERE mobile_number = '%s' LIMIT 1", $mobile));
	
	if(isset($otp_val))
		return $otp_val;
	
	return false;
}



//$mobile = $_POST['mobile_no'];
$mobile = "7905217012";


$otp = null;

$otp = findOtp($mobile);

if(!isset($otp) || $otp == false){
	$otp = generateOtp(6);
	insertInTable($mobile, $otp);
}


$message_template = "You have initiated a request at Min Bazaar. Your OTP is {OTP_VAL} DONT share it with anyone.";
$message = str_replace("{OTP_VAL}", $otp, $message_template);
$result = send_bulksms($mobile, $message);

