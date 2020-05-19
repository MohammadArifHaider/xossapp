<?php

require 'bdapps_cass_sdk.php';

$appid = "APP_024542";
$apppassword = "c48a35689fb237bdc7aa85626556b5dc";
$user_mobile = $_POST['user_mobile'];
$charge = $_POST['charge'];
$response=array();

//file_put_contents('ussd.txt',$_SERVER['REMOTE_ADDR']);

$production=true;

	if($production==false){
		$ussdserverurl ='http://localhost:7000/ussd/send';
	}
	else{
		$ussdserverurl= 'https://developer.bdapps.com/ussd/send';
	}

try{
	$subscription = new Subscription('https://developer.bdapps.com/subscription/send',$apppassword,$appid);
	$subscription_status = $subscription->getStatus('tel:88'.$user_mobile);
	//echo $subscription_status;
	if($subscription_status==="REGISTERED")
	{
  
  $caas = new DirectDebitSender("https://developer.bdapps.com/caas/direct/debit",$appid,$apppassword);
  $cass_status = $caas->cass($user_mobile,'tel:88'.$user_mobile,$charge);
  if($cass_status === 'ok')
  {
	array_push($response,array('response'=>'charged_successfull'));
  }
  else{
	array_push($response,array('response'=>'charged_unsuccessfull'));
  }
  //echo $cass_status;
	}
	else
	{
		array_push($response,array('response'=>'not_subscribe'));
	}




//array_push($response,array('response'=>'ok'));
echo json_encode($response);

}
catch (Exception $e){
	echo $e;
 
}
?>



