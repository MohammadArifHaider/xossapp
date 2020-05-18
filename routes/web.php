<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Route::get('/', function () {
    return view('welcome');
});

Route::get('faq',function()
{
   return view('faq');
}

);


Route::get('prize_pool',function()
{
   return view('prize_pool');
}

);

Route::post('subscription','UserController@subscription');

Route::post('ussd','UserController@ussd');

Route::post('sbscription_notification','UserController@subscription_notification');

Route::get('getMsisdn',"UserController@getMsisdn");
