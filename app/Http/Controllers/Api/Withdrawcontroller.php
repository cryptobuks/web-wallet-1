<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\User;
use App\Broker;
use App\Coin;
use App\Coin_address;
use App\Users_2;
use App\User_coin_2;
use App\Http\Requests;
use Tymon\JWTAuth\JWTAuth;
use App\Http\Controllers\Controller;
use Tymon\JWTAuth\Exceptions\JWTException;
use Curl\Curl;
use Validator;

class Withdrawcontroller extends Controller
{
    //
    private $user;
	private $users_2;
	private $coin;
	private $user_coin_2;
	private $coin_address;
	private $jwtauth;

	public function __construct(User $user, Users_2 $users_2, JWTAuth $jwtauth, Coin_address $coin_address, Coin $coin, User_coin_2 $user_coin_2){
		$this->user = $user;
		$this->jwtauth = $jwtauth;
		$this->coin = $coin;
		$this->user_coin_2 = $user_coin_2;
		$this->users_2 = $users_2;
		$this->coin_address = $coin_address;
	}

	
}
