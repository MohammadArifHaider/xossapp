<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller; 
use App\User; 
use Illuminate\Support\Facades\Auth; 
use Validator;

use Illuminate\Support\Facades\DB;

use App\daily_charging;
use App\premium_charging;
use App\point_table_regular;
use App\point_table_premium;
use App\withdraw;
use App\Classes\Logger;
use App\Classes\Subscription;
use App\Classes\SubscriptionException;
use App\Classes\UssdReceiver;
use App\Classes\UssdSender;
use App\Classes\UssdException;
use App\Classes\SMSSender;
use App\Classes\SMSReceiver;
use App\Classes\SMSServiceException;
use App\ussd_user;


class UserController extends Controller
{
    //

    public $successStatus = 200;
    public $app_id = "APP_017166";
    public $app_password = "85d518f39b54d61a2f49ce1160e936f1";

     public function login(){ 
        if(Auth::attempt(['mobile' => request('mobile'), 'password' => request('password')])){ 
            $user = Auth::user(); 
            $success['token'] =  $user->createToken('quiz')-> accessToken; 
            $success['id'] =  $user->id;
            return response()->json(['error'=>'no','user' => $success]); 
        } 
        else{ 
            return response()->json(['error'=>'Unauthorised'], 401); 
        } 
    }
    
    public function check_subscription(Request $request)
    {
        $user_id = $request->user_id;
        
        $user_mobile = User::where('id','=',$user_id)->first()->mobile;
        $subscription = new Subscription('https://developer.bdapps.com/subscription/send',$this->app_id,$this->app_password);
        $status = "UNREGISTERED";
        try
        {
            $status = $subscription->getStatus('tel:88'.$user_mobile);
        }
        catch(Exception $e)
        {
            
        }
        
        return response()->json(['response'=>$status]); 
        
        
    }
    
    public function subscription(Request $request)
    {
         $user_id = $request->user_id;
        
        $user_mobile = User::where('id','=',$user_id)->first()->mobile;
        $subscription = new Subscription('https://developer.bdapps.com/subscription/send',$this->app_id,$this->app_password);
        $subscription->subscribe('tel:88'.$user_mobile);
         return response()->json(['response'=>"ok"]); 
        
    }
    public function otp_login(Request $request)
    {
        $mobile_number =$request->mobile_number;
        $user = User::where('mobile','=',$mobile_number)->first();
        if($user)
        {
           
            $success['token'] =  $user->createToken('quiz')-> accessToken; 
            $success['id'] =  $user->id;
            return response()->json(['error'=>'no','user' => $success]);
        }
        else{
             return response()->json(['error'=>'number not registered']); 
        }
    }
    
    public function version_code(Request $request)
    {
        $code = $request->code;
        $version = DB::table('version_control')->where('version_code','=',$code)->first();
        
        return response()->json(['active'=>$version->active,'app_link'=>'https://bit.ly/2Ul13wh']);
    }
    public function set_withdraw_number(Request $request)
    {
        $user_id = $request->user_id;
        $withdraw_number = $request->withdraw_number;
        
        User::where('id','=',$user_id)->update(['withdraw_number'=>$withdraw_number]);
        
        return response()->json(['response'=>'ok']); 
    }
    
    public function edit_profile(Request $request)
    {
        $image = $request->image;
        $name = $request->name;
        $user_id = $request->user_id;
        $mobile = User::where('id','=',$user_id)->first()->mobile;
         $upload_path = "user_image/".$mobile.".jpg";
       
       file_put_contents($upload_path,base64_decode($image));
       
       $tmp_image = "http://www.fff-bd.com/quizhunt/user_image/".$mobile.".jpg";
       
      if (User::where('id','=',$user_id)->update(['name'=>$name,'image'=>$tmp_image]))
      {
          return response()->json(['response'=>'ok']);
      }
       else
       {
            return response()->json(['response'=>'not_ok']);     
       }
        
        
    }
    
    public function get_profile(Request $request)
    {
        $user_id = $request->user_id;
        $name = User::where('id','=',$user_id)->first()->name;
        $mobile = User::where('id','=',$user_id)->first()->mobile;
        $user = User::where('id','=',$user_id)->first();
        $withdraw_number = $user->withdraw_number;
        $image = User::where('id','=',$user_id)->first()->image;
        $total_point_regular = DB::table('point_regular_user')->where('user_id','=',$user_id)->first();
        $total_withdraw_regular = DB::table('withdraw_point_regular')->where('user_id','=',$user_id)->first();
           
         if(!$total_point_regular)
         {
             $regular_point = 0;
         }
         else{
           $regular_point =   $total_point_regular->point; 
         }
         
         if(!$total_withdraw_regular)
         {
             $regular_withdraw = 0;
         }
         else
         {
              $regular_withdraw =  $total_withdraw_regular->withdraws_point; 
         }
         
        $current_point_regular = $regular_point - $regular_withdraw;
        
        
        $total_point_premium = DB::table('point_premium_user')->where('user_id','=',$user_id)->first();
        $total_withdraw_premium = DB::table('withdraw_point_premium')->where('user_id','=',$user_id)->first();
        
           
        
        
        
          
         if(!$total_point_premium)
         {
             $premium_point = 0;
         }
         else{
           $premium_point =   $total_point_premium->point; 
         }
         
         if(!$total_withdraw_premium)
         {
             $premium_withdraw = 0;
         }
         else
         {
              $premium_withdraw =  $total_withdraw_premium->withdraws_point; 
         }
        
         $current_point_premium = $premium_point - $premium_withdraw;  
         
         if(!$withdraw_number)
         {
             $withdraw_number = "null";
         }
        return response()->json(['name'=>$name,'mobile'=>$mobile,'withdraw_number'=>$withdraw_number,'image'=>$image,'current_point_regular'=>$current_point_regular,'current_point_premium'=>$current_point_premium]);
           
        
    }
    
    public function check_otp(Request $request)
    {
        $mobile = $request->mobile;
        $otp = $request->otp;
        
        $valid = User::where('mobile','=',$mobile)->where('otp','=',$otp)->first();
        if($valid)
        {
            return response()->json(['response'=>'ok']);
        }
        else
        {
            return response()->json(['response'=>'not_ok']);
        }
    }
    
    public function send_otp(Request $request)
    {   
       // $mobile = '01845318609';
        $mobile_number = "tel:88".$request->mobile;
        $server = 'https://developer.bdapps.com/sms/send';
        $sender = new SMSSender($server,$this->app_id,$this->app_password);
        $otp = mt_rand(1000,9999);
        $sender->sms($otp,$mobile_number);
        User::where('mobile','=',$request->mobile)->update(['otp'=>$otp]);
        return response()->json(['response'=>'ok']);
        
    }
    

    public function register(Request $request)
    {

    	$validator = Validator::make($request->all(), [ 
            'name' => 'required', 
            'mobile' => 'required|unique:users', 
            'password' => 'required', 
            'city'=>'required',
            'image'=>'required',
            
        ]);

        if ($validator->fails())
        {
        	return response()->json(['error'=>$validator->errors()]); 
        }
        
        else{
   $input = $request->all(); 
                 
   
        
        $input['password'] = bcrypt($input['password']); 
      
       $mobile_number = "tel:88".$request->mobile;
       $upload_path = "user_image/".$input['mobile'].".jpg";
       
       file_put_contents($upload_path,base64_decode($input['image']));
       
       $input['image'] = "http://www.fff-bd.com/quizhunt/user_image/".$input['mobile'].".jpg";
       
        $user = User::create($input); 
       
       
       
         
      $subscription = new Subscription('https://developer.bdapps.com/subscription/send',$this->app_id,$this->app_password);
            
        
        
        
            try{
            $x = $subscription->subscribe($mobile_number);
            }
            catch(exception $e)
            {
                file_put_contents("error.txt",$e);
            }
            
           // file_put_contents("test.txt",$subscription);
            
            //file_put_contents("test.txt",$x." ".$mobile_number);
             
      //return $x;
        
        $success['token'] =  $user->createToken('quiz')-> accessToken; 
        $success['id'] =  $user->id;
      return response()->json(['user'=>$success]); 
      
        }
 

    }


     public function details() 
    { 
        $user = Auth::user(); 
        return response()->json(['success' => $user], $this->successStatus); 
    } 
    
     public function ussd()
    {
        
        //return $a;
        
    

    $production=true;

	if($production==false){
		$ussdserverurl ='http://localhost:7000/ussd/send';
	}
	else{
		$ussdserverurl= 'https://developer.bdapps.com/ussd/send';
	}

try{
    $receiver 	= new UssdReceiver();
    $ussdSender = new UssdSender($ussdserverurl,$this->app_id,$this->app_password);
    $subscription = new Subscription('https://developer.bdapps.com/subscription/send',$this->app_id,$this->app_password);


// ile_put_contents('text.txt',$receiver->getRequestID());
//$operations = new Operations();

//$receiverSessionId  =   $receiver->getSessionId();
$content 			= 	$receiver->getMessage(); // get the message content
$address 			= 	$receiver->getAddress(); // get the ussdSender's address
$requestId 			= 	$receiver->getRequestID(); // get the request ID
$applicationId 		= 	$receiver->getApplicationId(); // get application ID
$encoding 			=	$receiver->getEncoding(); // get the encoding value
$version 			= 	$receiver->getVersion(); // get the version
$sessionId 			= 	$receiver->getSessionId(); // get the session ID;
$ussdOperation 		= 	$receiver->getUssdOperation(); // get the ussd operation






//file_put_contents('status.txt',$address);

$responseMsg = " Thank you for your Subscription.";


if ($ussdOperation  == "mo-init") {
	   
	   try{
	       	$ussdSender->ussd($sessionId, $responseMsg,$address,'mt-fin');
		    $x = $subscription->subscribe($address);
		    ussd_user::create(['user_mobile'=>$address]);
	   }
	   catch(Exception $e)
	   {
	       
	   }
  }
   
	


}
catch (Exception $e){
    file_put_contents('USSDERROR.txt',$e);   
}
        
    }
    
    public function subscription_notification()
    {
        file_put_contents('test.txt','hello');
    }
   
   
   public function getMsisdn(Request $request){
       
	$msisdn = $request->header('msisdn');
	
	//return $msisdn;

	if(!isset($msisdn)){
        $msg = "Please use mobile data of Robi or Airtel operator!";
        return ('error');
    }else{
    	return compact('msisdn');
      }
      }
}
