<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;




use App\Http\Controllers\Controller; 
use App\User; 
use Illuminate\Support\Facades\Auth; 
use Validator;

use App\Classes\Logger;

use App\Classes\DirectDebitSender;
use App\Classes\CassException;

use App\question;

use App\Classes\Subscription;
use App\Classes\SubscriptionException;
use App\daily_charging;
use App\premium_charging;
use App\point_table_regular;



class QuestionController extends Controller
{
    public $app_id = "APP_017166";
    public $app_password = "85d518f39b54d61a2f49ce1160e936f1";

  //public $date = date('d-m-Y');
    
    public function caas_charge_regular ($externalTrxId,$mobile_number,$amount)
    { 
        
         $subscription = new Subscription('https://developer.bdapps.com/subscription/send',$this->app_id,$this->app_password);
        
          try{
            $x = $subscription->subscribe("tel:88".$mobile_number);
            }
            catch(exception $e)
            {
                file_put_contents("error.txt",$e);
            }
            try{
                
	    $caas = new DirectDebitSender();
      $cass_status = json_decode($caas->cass($externalTrxId, "tel:88".$mobile_number,$amount));
      
      
      //return $cass_status->statusCode;
      
      if($cass_status->statusCode === "S1000")
      {
    daily_charging::create(['statusCode'=>$cass_status->statusCode,'timeStamp'=>$cass_status->timeStamp,'externalTrxId'=>$cass_status->externalTrxId,'statusDetail'=>$cass_status->statusDetail,'internalTrxId'=>$cass_status->internalTrxId]);
     
      return "ok";
      }
     else
     {
         return "not_ok";
     }
      
     
	}
	catch(exception $e)
	{
	    
	    
	   //return $e->getStatusCode; 
	}
    }
    
    
     public function caas_charge_premium ($externalTrxId,$mobile_number,$amount)
    {
        
         $subscription = new Subscription('https://developer.bdapps.com/subscription/send',$this->app_id,$this->app_password);
        
          try{
            $x = $subscription->subscribe("tel:88".$mobile_number);
            }
            catch(exception $e)
            {
                file_put_contents("error.txt",$e);
            }
            
            try{
                
	    $caas = new DirectDebitSender();
      $cass_status = json_decode($caas->cass($externalTrxId, "tel:88".$mobile_number,$amount));
      //return $cass_status->statusCode;
      //file_put_contents('cass.txt',$cass_status->statusCode);
      
      if($cass_status->statusCode === "S1000")
      {
    premium_charging::create(['statusCode'=>$cass_status->statusCode,'timeStamp'=>$cass_status->timeStamp,'externalTrxId'=>$cass_status->externalTrxId,'statusDetail'=>$cass_status->statusDetail,'internalTrxId'=>$cass_status->internalTrxId]);
     
      return "ok";
      }
     else
     {
         return "not_ok";
     }
      
     
	}
	catch(exception $e)
	{
	    
	    
	   //return $e->getStatusCode; 
	}
    }
    
    
    public function get_question(Request $request)
    {
        date_default_timezone_set('Asia/Dhaka');
         $date = date('Y-m-d');
         
        $subscription = new Subscription('https://developer.bdapps.com/subscription/send',$this->app_id,$this->app_password);
          
          
          
       
        $user_id = $request->user_id;
        
        $user_type = $request->user_type;
        $quiz_type = $request->quiz_type;
        $subject_id = $request->subject;
        // $question_limit = $request->question_limit;
        
        $mobile_number = User::where('id','=',$user_id)->first()->mobile;
       
        
        try{
            $status = $subscription->getStatus("tel:88".$mobile_number);
            
        }
        catch(Exception $e)
        {
            
        }
        
        if($status === "UNREGISTERED")
        {
            
                   $question = null;
                  $error = "unsubscribe"; 
                  return response()->json(['error'=>$error,'user_type'=>'free','question'=>$question]);
        }
        
        else if($status ==="PENDING CHARGE")
        {
            
                  $question = null;
                  $error = "pending_charge"; 
                  return response()->json(['error'=>$error,'user_type'=>'free','question'=>$question]);
            
            //   $t = $this->caas_charge_regular($user_id,$mobile_number,'0.01');
            //     if($t === "ok")
            //     {
            //         $question = question::inRandomOrder()->limit(10)->get();
                
            //         return response()->json(['error'=>'no','user_type'=>'free','question'=>$question]);
                    
            //     }
            //     else
            //     {
            //       $question = "null";
            //       $error = "insufficient balance"; 
            //       return response()->json(['error'=>$error,'user_type'=>'free','question'=>$question]);
            //     }
            
        }
        else
        {
            
             if($quiz_type === "regular")
        
        {
            
        
        if(!point_table_regular::where('user_id','=',$user_id)->where('date','=',$date)->first())
        {   
         
               $question = question::where('subject_id','=',$subject_id)->inRandomOrder()->limit(5)->get();
            	return response()->json(['error'=>'no','user_type'=>'free','question'=>$question]);
               	
        }
        
        else
        {
           
            
          if($user_type === 'paid')
            {
                $t = $this->caas_charge_regular($user_id,$mobile_number,'0.1');
                if($t === "ok")
                {
                    $question = question::where('subject_id','=',$subject_id)->inRandomOrder()->limit(5)->get();
                    return response()->json(['error'=>'no','user_type'=>'paid','question'=>$question]);
                    
                }
                else
                {
                   return response()->json(['error'=>'insufficent balance']);
                }
             
                
            }
            
           else
           {
                return response()->json(['error'=>'no','user_type'=>'paid']);
           }
        }
        
    }
    
    else{
        
        $t = $this->caas_charge_premium($user_id,$mobile_number,'0.1');
        if($t === "ok")
        {
            $question = question::inRandomOrder()->limit(1)->get();
             return response()->json(['error'=>'no','user_type'=>'paid','question'=>$question]);
        }
        
        else{
           return response()->json(['error'=>'insufficent balance']);
        }
    }
    
            
            
        }
        
        
        
       
    
        
        
       
    
        
    }
    
}
