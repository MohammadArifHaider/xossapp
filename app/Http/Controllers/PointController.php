<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;




use App\Http\Controllers\Controller; 
use App\User; 
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\DB;
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
use App\point_table_premium;
use App\withdraw;
use App\point_table_live;




class PointController extends Controller
{
   public $app_id = "APP_017166";
    public $app_password = "85d518f39b54d61a2f49ce1160e936f1";

  //public $date = date('d-m-Y');
    
    
    public function live_leaderboard(Request $request)
    {
        
        $userid = $request->user_id;
       $user = User::where('id','=',$userid)->first();
       $user_name = $user->name;
       $user_image = $user->image;
         
   $live_contest = DB::table('live_contest')->where('status','=',1)->first();
       $live_contest_id = $live_contest->id;
    $leaderboard = point_table_live::where('live_contest_id','=',$live_contest_id)->orderBy('point','DESC')->get();
    $leader = array();
       
            
    $user_point = "-";
    $user_rank = "-";

    for($i=0;$i<sizeof($leaderboard);$i++)
    {
          $user_id = $leaderboard[$i]->user_id;
           $user = User::where('id','=',$user_id)->first();
           
           if($user_id == $userid)
           {
               $user_point = $leaderboard[$i]->point;
               $user_rank = $i+1;
               $user_rank = (string)$user_rank;
           }
           
            if($user)
           {
           $name = $user->name;
           $image = $user->image;
           
           $leader[] = array('name'=>$name,
           'image'=>$image,
           'point'=>$leaderboard[$i]->point,
           'rank'=>(string)$i+1
           
           );
           }
           else 
           {
               $leader[]= array('name'=>'null',
               'image'=>'null',
               'point'=>'null',
               'rank'=>'null');
           }
    }
    
    
    return response()->json(['user_point'=>$user_point,'user_rank'=>$user_rank,'user_name'=>$user_name,'user_image'=>$user_image,'leaderboard'=>$leader]);
    
    }
    
   
   public function leaderboard(Request $request)
   {
       $user_id = $request->user_id;
       $quiz_type = $request->quiz_type;
       
       $user = User::where('id','=',$user_id)->first();
       
       $user_name = $user->name;
       $user_image = $user->image;
       
       if($quiz_type === 'regular')
       { 
          // $user_point = DB::table('monthly_point_regular_user')->where('user_id','=',$user_id)->first()->point;
           
            $user = DB::table('monthly_point_regular_user')->where('user_id','=',$user_id)->first();
            
            if($user)
            {
                $user_point = $user->point;
                $user_rank = $user->rank;
            }
            
            else
            {
                $user_point = 0;
                $user_rank = 0;
            }
            
            
            
          // $user_rank = DB::table('monthly_point_regular_user')->where('user_id','=',$user_id)->first()->rank;
          $leaderboard = DB::table('monthly_point_regular_user')->orderBy('rank', 'ASC')->limit(20)->get();  
          
       }
       else
       {  
         $user = DB::table('monthly_point_premium_user')->where('user_id','=',$user_id)->first();
        //   $user_rank = DB::table('monthly_point_premium_user')->where('user_id','=',$user_id)->first()->rank;
           
           if($user)
           {
               $user_point = $user->point;
               $user_rank = $user->rank;
           }
           else
           {
               $user_point = "-";
               $user_rank = "-";
           }
           
           $leaderboard = DB::table('monthly_point_premium_user')->orderBy('rank', 'ASC')->limit(20)->get(); 
       }
       
       $leader = array();
       
       for($i=0;$i<sizeof($leaderboard);$i++)
       {
           $user_id = $leaderboard[$i]->user_id;
           $user = User::where('id','=',$user_id)->first();
           
           if($user)
           {
           $name = $user->name;
           $image = $user->image;
           
           $leader[] = array('name'=>$name,
           'image'=>$image,
           'point'=>$leaderboard[$i]->point,
           'rank'=>$leaderboard[$i]->rank
           
           );
           }
           else 
           {
               $leader[]= array('name'=>'null',
               'image'=>'null',
               'point'=>'null',
               'rank'=>'null');
           }
       }
       
       
       
       return response()->json(['user_point'=>$user_point,'user_rank'=>$user_rank,'user_name'=>$user_name,'user_image'=>$user_image,'leaderboard'=>$leader]);
       
       
   }
   
   public function get_live_quiz_news(Request $request)
   {
       $live_contest = DB::table('live_contest')->where('status','=',1)->first();
       
       if($live_contest)
       {
           $news = $live_contest->news;
           $banner = $live_contest->image;
           
           return response()->json(['news'=>$news,'banner'=>$banner]);
       }
       else
       {
           return response()->json(['error'=>"No data found"]);
       }
   }
   
   public function live_contest_answer_submit(Request $request)
   {
       $user_id = $request->user_id;
       $score = $request->score;
       $live_contest = DB::table('live_contest')->where('status','=',1)->first();
       $live_contest_id = $live_contest->id;
       
       
       point_table_live::where('user_id','=',$user_id)->where('live_contest_id','=',$live_contest_id)->update(['point'=>$score]);
       
       return response()->json(['response'=>'ok']);
       
       
   }
   
   public function live_contest(Request $request)
   {
       
        date_default_timezone_set('Asia/Dhaka');
       $user_id = $request->user_id;
       
        $live_contest = DB::table('live_contest')->where('status','=',1)->first();
        
        if($live_contest)
        {
            $live_contest_id = $live_contest->id;
             
                if(point_table_live::where('live_contest_id','=',$live_contest_id)->where('user_id','=',$user_id)->first())
                {
                 
                    return response()->json(['error'=>'Already participated']);
                }
                else{
                     
                     
                     $starting_time = $live_contest->starting_time;
                     $ending_time = $live_contest->ending_time;
                     $duration = $live_contest->duration;
                     $temp_duration = '0.'.$duration;
                     
                     
                   
                     $current_time = date('H.i');
                     
                     
                    
                     
                     
                     
                     
                     if($starting_time>$current_time)
                     {
                         $remaining_time = $starting_time - $current_time;
                         return response()->json(['error'=>'Contest is not starting']);
                     }
                     else if($current_time>$ending_time)
                     {
                         return response()->json(['error'=>'Contest Finished']);
                     }
                  else if(($ending_time-$current_time)<1)
                     
                     {
                           
                         
                        if(($ending_time-$current_time)*100<$duration)
                        {
                             point_table_live::create(['user_id'=>$user_id,'live_contest_id'=>$live_contest_id]); 
                            $duration =  ($ending_time-$current_time)*100;
                            $duration = round($duration,2);
                            $question = question::inRandomOrder()->limit(20)->get();
                        
                             return response()->json(['duration'=>$duration,'question'=>$question]);
                             
                        }
                        else
                        {
                             point_table_live::create(['user_id'=>$user_id,'live_contest_id'=>$live_contest_id]); 
                             return response()->json(['duration'=>$duration,'question'=>$question]);
                        }
                        
                     }
                     
                    else{
                         point_table_live::create(['user_id'=>$user_id,'live_contest_id'=>$live_contest_id]); 
                          $question = question::inRandomOrder()->limit(20)->get();
                      
                      return response()->json(['duration'=>$duration,'question'=>$question]);
                    }
                     
                    
                     
                    
                }
       
        }
        
       
       
       
   }
    
    public function submit_answer(Request $request)
    {       
          date_default_timezone_set('Asia/Dhaka');
         $date = date('Y-m-d');
        
           $user_id = $request->user_id;
           $quiz_type = $request->quiz_type;
           $point = $request->point;
           
           if($quiz_type === "regular")
           {
           point_table_regular::create(['user_id'=>$user_id,'point'=>$point,'date'=>$date]);
           
           return response()->json(['response'=>'ok']);
           }
           
           else
           {
              point_table_premium::create(['user_id'=>$user_id,'point'=>$point]);
              return response()->json(['response'=>'ok']);
           }
          
        
    }
    
    public function withdraw_request(Request $request)
    {
        $user_id = $request->user_id;
           $quiz_type = $request->quiz_type;
           $amount = $request->amount;
           
           $user = User::where('id','=',$user_id)->first();
           
           if($user->withdraw_number)
           {
           withdraw::create(['user_id'=>$user_id,'quiz_type'=>$quiz_type,'withdraw_amount'=>$amount]);
           
           if($quiz_type === 'regular')
           {
           $total_point = DB::table('point_regular_user')->where('user_id','=',$user_id)->first()->point;
           $total_withdraw = DB::table('withdraw_point_regular')->where('user_id','=',$user_id)->first()->withdraws_point;
           
           $current_point = $total_point - $total_withdraw;
           }
           else{
               $total_point = DB::table('point_premium_user')->where('user_id','=',$user_id)->first()->point;
           $total_withdraw = DB::table('withdraw_point_premium')->where('user_id','=',$user_id)->first()->withdraws_point;
           
           $current_point = $total_point - $total_withdraw;
           }
           
           return response()->json(['response'=>'ok','quiz_type'=>$quiz_type,'current_point'=>$current_point]);
           }
           else{
                return response()->json(['response'=>'no_bkash','quiz_type'=>'null','current_point'=>'null']);
           }
    }
    
}
