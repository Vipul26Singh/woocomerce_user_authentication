<?php


include_once '../../../../wp-load.php';


define('AUTHORIZAION_KEY', 'ck_bca5ee0c5f916c12896590606abab1c4cee4cc08');
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
	$otp_val = $wpdb->get_var( $wpdb->prepare("SELECT otp_value FROM ".$wpdb->prefix."minbazaar_otp WHERE mobile_number = '%s' and used!=1 LIMIT 1", $mobile));
	
	if(isset($otp_val))
		return $otp_val;
	
	return false;
}

function mobile_already_exists($mobile){
                global $wpdb;
                $user_count = $wpdb->get_var( $wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix."usermeta WHERE meta_key = 'mobile_numer' and meta_value = '%s'", $mobile));


                if($user_count > 0)
                        return true;
                else
                        return false;
        }

function validate_mobile($mobile){
	if (!isset($mobile) || empty($mobile) || $mobile==null) {
                                return "Mobile Number is required";
        }else if( ( strlen($mobile) != 10 || !preg_match('/^[0-9]{10}$/', $mobile) )){
                                return "Enter 10 digit mobile number";
	}else if( mobile_already_exists($mobile)){
                                return "Mobile Number already registered";
        }
	return null;
}


$jsonData = json_decode(file_get_contents('php://input'), true);


$mobile = $jsonData['mobile_no'];
$auth_key = $jsonData['auth_key'];

if(!isset($auth_key) || $auth_key != AUTHORIZAION_KEY){
	echo "You are not authorised";
	return "You are not authorised";
}

if(!isset($jsonData['validate']) || strcasecmp($jsonData['validate'], "YES") !=0){
	$validation_val = validate_mobile($mobile);

	if(isset($validation_val)){
		echo $validation_val;
		return $validation_val;
	}


	$otp = null;

	$otp = findOtp($mobile);

	if(!isset($otp) || $otp == false){
		$otp = generateOtp(6);
		insertInTable($mobile, $otp);
	}


	$message_template = "You have initiated a request at Min Bazaar. Your OTP is {OTP_VAL} DONT share it with anyone.";
	$message = str_replace("{OTP_VAL}", $otp, $message_template);
	$result = send_bulksms($mobile, $message);
}else{
	$mobile = $jsonData['mobile_no'];
	$otp = $jsonData['OTP'];
	global $wdb;
	$otp_count = $wpdb->get_var( $wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix . "minbazaar_otp WHERE mobile_number = '%s' and otp_value = '%s' and used != 1", $mobile, $otp));

	if($otp_count > 0){
		$otp_count = $wpdb->get_var( $wpdb->prepare("UPDATE ".$wpdb->prefix . "minbazaar_otp set used = 1 WHERE mobile_number = '%s' and otp_value = '%s' ", $mobile, $otp));
		echo 1;
	}else{
		echo 0;
	}
}

