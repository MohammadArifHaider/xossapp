<?php

require 'bdapps_cass_sdk.php';

$appid = "APP_024542";
$apppassword = "c48a35689fb237bdc7aa85626556b5dc";
$user_mobile = $_POST['user_mobile'];


//file_put_contents('ussd.txt',$_SERVER['REMOTE_ADDR']);

$production=true;

	if($production==false){
		$ussdserverurl ='http://localhost:7000/ussd/send';
	}
	else{
		$ussdserverurl= 'https://developer.bdapps.com/ussd/send';
	}

try{
//$receiver 	= new UssdReceiver();
//$ussdSender = new UssdSender($ussdserverurl,$appid,$apppassword);
$subscription = new Subscription('https://developer.bdapps.com/subscription/send',$apppassword,$appid);
//$status = $subscription->getStatus($address);
$subscription->subscribe('tel:88'.$user_mobile);

$response =array('response'=>'ok');

//$responseMsg = ($status == "REGISTERED")? "1. unsubscribe" : " Thank you for your Subscription.";

echo json_encode($response);

}
catch (Exception $e){
	echo $e;
 //file_put_contents('USSDERROR.txt','Some error occured');   
}
?>



