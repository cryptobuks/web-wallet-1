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
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
Route::post('register', 'Api\AuthController@register');
Route::post('login', 'Api\AuthController@login');

Route::group(['middleware' => ['jwt.auth']], function() {
	Route::get('logout', 'Api\AuthController@logout');
	Route::post('password/email', 'Auth\ForgotPasswordController@getResetToken');
	Route::post('password/reset', 'Auth\ResetPasswordController@reset');
	Route::post('get_address', 'Api\Coincontroller@address_generation');
	Route::post('hot_balance', 'Api\Coincontroller@online_balance');
	Route::post('cold_balance', 'Api\Coincontroller@offline_balance');
	Route::post('createraw_hash', 'Api\Coincontroller@create_raw');
	Route::get('test', function(){
		return response()->json(['foo'=>'bar']);
	});
});