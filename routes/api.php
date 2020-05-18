<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('register', 'UserController@register');

Route::post('login', 'UserController@login');
Route::post('otp_login','UserController@otp_login');

Route::post('send_otp','UserController@send_otp');

Route::post('check_otp','UserController@check_otp');


Route::post('version_code','UserController@version_code');


Route::group(['middleware' => 'auth:api'], function()
{
    Route::post('set_withdraw_number', 'UserController@set_withdraw_number');
   Route::post('details', 'UserController@details');
   
   
   Route::post('get_live_quiz_news','PointController@get_live_quiz_news');
   
   Route::post('edit_profile','UserController@edit_profile');
   
   Route::post("get_question",'QuestionController@get_question');
   
   Route::post("submit_answer",'PointController@submit_answer');
   
   Route::post("withdraw_request",'PointController@withdraw_request');
   
    Route::post("get_profile",'UserController@get_profile');
    
     Route::post("leaderboard",'PointController@leaderboard');
     
     Route::post("live_leaderboard",'PointController@live_leaderboard');
     
     
     
     Route::post("live_contest",'PointController@live_contest');
     
     Route::post('live_contest_answer_submit','PointController@live_contest_answer_submit');
     
     
     Route::post('check_subscription','UserController@check_subscription');
     
     Route::post('subscription','UserController@subscription');
       
   
    
   
   
   
   
});

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
