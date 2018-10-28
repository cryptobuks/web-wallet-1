<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\User;
use App\Broker;
use App\Coin;
use App\Coin_address;
use App\Users_2;
use App\Http\Requests;
use Tymon\JWTAuth\JWTAuth;
use App\Http\Controllers\Controller;
use Tymon\JWTAuth\Exceptions\JWTException;
use Curl\Curl;
use Validator;

class Coincontroller extends Controller
{
    //
  private $user;
  private $coin;
  private $coin_address;
  private $jwtauth;

  public function __construct(User $user, JWTAuth $jwtauth, Coin_address $coin_address, Coin $coin){
	$this->user = $user;
	$this->jwtauth = $jwtauth;
	$this->coin = $coin;
	$this->coin_address = $coin_address;
}

	/*-- Withdraw_fees --*/
	public function set_withdraw(Request $request){
		$user = $this->jwtauth->parseToken()->authenticate();
      	if($user->is_admin == 1 || $user->is_broker == 1){

      		//Withdraw Fees Update
			if($request->msg == "fees"){
		      	$request->validate([
		            'broker_id' => 'required',
		            'broker_username' => 'required',
		            'coin' => 'required',
		            'coinid' => 'required',
		            'withdraw_fees' => 'required',
		        ]);
				$coinss = $this->coin->where(['broker_id'=>$request->broker_id,'broker_username'=>$request->broker_username,'id'=>$request->coinid,'coin'=>$request->coin])->get(['broker_id','id','coin']);
				if($coinss == '[]'){
					return response()->json(['success'=> false, 'message'=> 'Coin Not Exist']);
		        }
		        else{
		        	$updated = $this->coin->where(['broker_id'=>$request->broker_id,'id'=>$request->coinid,'coin'=>$request->coin])->update(['withdraw_fees' => $request->withdraw_fees]);
		        if (!$updated) {
     		 		return response()->json(['failed_to_add_data'], 500);
    			}
    			else{
					return response()->json(['success'=> true, 'message'=> 'Thanks for submitting data.']);
    				}
		        }
		    }

      		//Withdraw Fees Update
      	elseif($request->msg == "minimum"){
  			$rules = [
	            'broker_id' => 'required|numeric',
	            'broker_username' => 'required',
	            'coin' => 'required',
	            'coinid' => 'required|numeric',
	            'min_withdraw' => 'required',
	        ];
	        $input = $request->only(
	            'broker_id',
	            'broker_username',
	            'coin',
	            'coinid',
	            'min_withdraw'
	        );
	        $validator = Validator::make($input, $rules);
	        if($validator->fails()) {
	            $error = $validator->messages()->toJson();
	            return response()->json(['success'=> false, 'error'=> $error]);
	        }
	        else{
	        	$broker_id = $request->broker_id;
	        	$broker_username = $request->broker_username;
        		$coin = $request->coin;
        		$coinid = $request->coinid;
        		$min_withdraw = $request->min_withdraw;
        		$coinss = $this->coin->where(['broker_id'=>$broker_id,'broker_username'=>$broker_username,'id'=>$coinid,'coin'=>$coin])->get();
        		if($coinss == '[]'){
					return response()->json(['success'=> false, 'message'=> 'Coin Not Exist']);
        		}
        		else{
 					$updated = $this->coin->where(['broker_id'=>$broker_id,'id'=>$coinid,'coin'=>$coin])->update(['min_withdraw' => $min_withdraw]);
			        if (!$updated) {
	     		 		return response()->json(['failed_to_add_data'], 500);
	    			}
	    			else{
	    				return response()->json(['success'=> true, 'message'=> 'Thanks for submitting data.']);
	    				}
        			}
      			}
      		}
      		else {
      			return response()->json(['success'=> false, 'message'=> 'Method Not Found']);
      		}
      	}
      	else{
      		return response()->json(['success'=> false, 'message'=> 'Access Denied']);
      	}
	}

	public function address_generation(Request $request){
		$user = $this->jwtauth->parseToken()->authenticate();
		if($user->is_broker == 1){
			$request->validate([
				'broker_id' => 'required|numeric',
				'coin' => 'required',
				'userid' => 'required|numeric',
				'username' => 'required|max:40'
			]);
			$coin = $this->coin->where(['broker_id'=>$user->id,'coin'=>$request->coin])->get(['id','coin','deposits','api']);
			if($coin == "[]"){
				return response()->json(['success'=> false, 'message'=> 'Invalid coin']);
			}
			/*---- Main ---- */
			else{
				$coin_data = json_encode($coin);
				$coin_data = substr($coin_data,1);
				$coin_data = substr_replace($coin_data,"", -1);
				$coin_data = (json_decode($coin_data,true));
				if($coin_data['deposits'] == 0 || is_null($coin_data['api'])){
					return response()->json(['success'=> false, 'message'=> 'Deposists Disabled']);
				}
				else{
					$exchange = 'users_'.$user->id;
					if(isset($this->$exchange)){
						$user_basic_data = $this->$exchange->where(['broker_id'=>$request->broker_id,'id'=>$request->userid,'username'=>$request->username])->whereIn('verified',[2,3,4,5])->get(['id','username','verified']);
						if($user_basic_data == '[]'){
							return response()->json(['success'=> false, 'message'=> 'User Not Found/Verified']);
						}
						else{
							$coin_addresses = $this->coin_address->where(['coin'=>$request->coin,'broker_id'=>$user->id,'broker_username'=>$user->username,'userid'=>$request->userid,'username'=>$request->username])->get(['id','address','message','userid','username','broker_id','coin','coin_id']);
							if($coin_addresses == "[]"){
								$curl = new Curl();
								$auth_tok = $this->jwtauth->fromUser($user);
								$auth_token =  "Bearer ".$auth_tok;
								$curl->setHeader("Content-Type","application/json");
								$curl->setHeader("Accept","application/json");
								$curl->setHeader("Authorization",$auth_token);
								$url_new = $coin_data['api'].'add_gen_'.$request->broker_id;
								$curl->post($url_new,array(
					        		'key'=>md5('affan'),
					        		'coinid'=>$coin_data['id'],
					        		'coin'=>$coin_data['coin'],
					        		'username'=>$request->username
					        	));
					        	if($curl->error){
					        		$this->jwtauth->invalidate($auth_tok);
					        		return response()->json(['success'=>false,'message'=>'Server Error...']);
					        	}
					        	else{
					        		$this->jwtauth->invalidate($auth_tok);
					        		$getaddress = ($curl->response);
					        		if($getaddress->success == false){
					        			return response()->json(['success'=>false,'message'=>$getaddress->message]);
					        		}
					        		elseif(isset($getaddress->address)){
					        			$added =  $this->coin_address->create(['broker_id'=>$user->id,'broker_username'=>$user->username,'coin'=>$coin_data['coin'],'coin_id'=>$coin_data['id'],'address'=>$getaddress->address,'message'=>$getaddress->msg,'userid'=>$request->userid,'username'=>$request->username]);
								 		if(!$added){
					 		 				return response()->json(['failed_to_add_data'], 500);
								 		}
								 		else{
								 			return response()->json($added);
								 		}
					        		}
					        		else{
					        			return response()->json(['success'=>false,'message'=>'Unknown Error Occured...']);
					        		}
					        	}
							}
							/*--- already exist ---*/
							else{
								$coin_addresses = json_encode($coin_addresses);
								$coin_addresses = substr($coin_addresses,1);
								$coin_addresses = substr_replace($coin_addresses,"", -1);
								$coin_addresses = json_decode($coin_addresses);
					        	return response()->json($coin_addresses);
							}
							/*--- already exist ---*/
						}
					}
					else{
						return json_encode(array(['success'=> false, 'message'=> 'Exchange Not Found']));
					}
				}
			}
			/*---- Main -----*/
		}
		else{
			return response()->json(['success'=> false, 'message'=> 'Access Denied !']);
		}
	}
	
/*-END-*/				
}