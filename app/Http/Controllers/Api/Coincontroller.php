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

	
	public function address_generation(Request $request){
		$user = $this->jwtauth->parseToken()->authenticate();
		if($user->is_verified == 1 && $user->id == 1){
			$request->validate([
				'coin' => 'required',
				'userid' => 'required|numeric',
				'username' => 'required|max:40'
			]);
			$coin = $this->coin->where(['coin'=>$request->coin])->get(['id','coin','deposits']);
			if($coin == "[]"){
				return response()->json(['success'=> false, 'message'=> 'Invalid coin']);
			}
			/*---- Main ---- */
			else{
				$coin_data = json_encode($coin);
				$coin_data = substr($coin_data,1);
				$coin_data = substr_replace($coin_data,"", -1);
				$coin_data = (json_decode($coin_data,true));
				if($coin_data['deposits'] == 0){
					return response()->json(['success'=> false, 'message'=> 'Deposists Disabled']);
				}
				else{
					$coin_addresses = $this->coin_address->where(['coin'=>$request->coin,'userid'=>$request->userid,'username'=>$request->username])->get(['id','address','message','userid','username','coin','coin_id']);
					if($request->coin == "KMD"){
						if($coin_addresses == "[]"){
							$getaddress = new Curl();
							$getaddress->post("http://127.0.0.1/komodo/komodo.php",array('msg' =>'getnewaddress' ,'key' =>'chow_address','coin'=>'kmd'));
							if($getaddress->errorMessage){
								return response()->json(['success'=>false,'message'=>'Coin network error']);
							}
							else{
								$dataa = json_decode($getaddress->response,true);
								if(isset($dataa['address'])){
									$added =  $this->coin_address->create(['coin'=>$coin_data['coin'],'coin_id'=>$coin_data['id'],'address'=>$dataa['address'],'message'=>'None','userid'=>$request->userid,'username'=>$request->username]);

									return response()->json(['success'=>true, 'network'=>$request->coin,'address'=>$dataa['address'],'msg'=>'None']);
								}
								else{
									return response()->json(['success'=>false,'message'=>'network error']);
								}
							}
				        }
		        		else{
							$coin_addresses = json_encode($coin_addresses);
							$coin_addresses = substr($coin_addresses,1);
							$coin_addresses = substr_replace($coin_addresses,"", -1);
							$coin_addresses = json_decode($coin_addresses,true);
					        return response()->json(['success'=>true,"network"=>$request->coin,"address"=>$coin_addresses["address"],"msg"=>'None']);
						}
					}
					else{
						return response()->json(['success'=> false, 'message'=> 'Access Denied !']);
					}
				}
			}
		}
		else{
			return response()->json(['success'=> false, 'message'=> 'Access Denied !']);
		}
	}

	public function all_balances(Request $request){
		$user = $this->jwtauth->parseToken()->authenticate();
		if($user->is_verified == 1 && $user->id == 1){
			$request->validate([
				'coin' => 'required'
			]);
			$coin = $this->coin->where(['coin'=>$request->coin])->get(['id','coin']);
			if($coin == "[]"){
				return response()->json(['success'=> false, 'message'=> 'Invalid coin']);
			}
			/*---- Main ---- */
			else{
				$coin_data = json_encode($coin);
				$coin_data = substr($coin_data,1);
				$coin_data = substr_replace($coin_data,"", -1);
				$coin_data = (json_decode($coin_data,true));
				if($coin_data['deposits'] == 0){
					return response()->json(['success'=> false, 'message'=> 'Deposists Disabled']);
				}
				else{
					$coin_addresses = $this->coin_address->where(['coin'=>$request->coin,'userid'=>$request->userid,'username'=>$request->username])->get(['id','address','message','userid','username','coin','coin_id']);
					if($request->coin == "KMD"){
						if($coin_addresses == "[]"){
							$getaddress = new Curl();
							$getaddress->post("http://127.0.0.1/komodo/komodo.php",array('msg' =>'getnewaddress' ,'key' =>'chow_address','coin'=>'kmd'));
							if($getaddress->errorMessage){
								return response()->json(['success'=>false,'message'=>'Coin network error']);
							}
							else{
								$dataa = json_decode($getaddress->response,true);
								if(isset($dataa['address'])){
									$added =  $this->coin_address->create(['coin'=>$coin_data['coin'],'coin_id'=>$coin_data['id'],'address'=>$dataa['address'],'message'=>'None','userid'=>$request->userid,'username'=>$request->username]);

									return response()->json(['success'=>true, 'network'=>$request->coin,'address'=>$dataa['address'],'msg'=>'None']);
								}
								else{
									return response()->json(['success'=>false,'message'=>'network error']);
								}
							}
				        }
		        		else{
							$coin_addresses = json_encode($coin_addresses);
							$coin_addresses = substr($coin_addresses,1);
							$coin_addresses = substr_replace($coin_addresses,"", -1);
							$coin_addresses = json_decode($coin_addresses,true);
					        return response()->json(['success'=>true,"network"=>$request->coin,"address"=>$coin_addresses["address"],"msg"=>'None']);
						}
					}
					else{
						return response()->json(['success'=> false, 'message'=> 'Access Denied !']);
					}
				}
			}
		}
		else{
			return response()->json(['success'=> false, 'message'=> 'Access Denied !']);
		}
	}

	public function online_balance(Request $request){
		$user = $this->jwtauth->parseToken()->authenticate();
		if($user->is_verified == 1 && $user->id == 1){
			$request->validate([
				'coin' => 'required'
			]);
			$coin = $this->coin->where(['coin'=>$request->coin])->get(['id','coin','deposits']);
			if($coin == "[]"){
				return response()->json(['success'=> false, 'message'=> 'Coin Not Found']);
			}
			/*---- Main ---- */
			else{
				$coin_data = json_encode($coin);
				$coin_data = substr($coin_data,1);
				$coin_data = substr_replace($coin_data,"", -1);
				$coin_data = (json_decode($coin_data,true));
				if($coin_data['deposits'] == 0){
					return response()->json(['success'=> false, 'message'=> 'Deposists Disabled']);
				}
				else{
					if($request->coin == "KMD"){
						$getaddress = new Curl();
						$getaddress->post("http://127.0.0.1/komodo/komodo.php",array('msg' =>'balance_main' ,'key' =>'kmd_main_blnc','coin'=>'kmd'));
						if($getaddress->errorMessage){
							return response()->json(['success'=>false,'message'=>'Coin network error']);
						}
						else{
							$dataa = json_decode($getaddress->response,true);
							if(isset($dataa['balnc'])){
								return response()->json(['success'=>true, 'network'=>$request->coin,'balance'=>$dataa['balnc']]);
							}
							else{
								return response()->json(['success'=>false,'message'=>'network error']);
							}
						}
					}
					else{
						return response()->json(['success'=> false, 'message'=> 'Access Denied !']);
					}
				}
			}
		}
		else{
			return response()->json(['success'=> false, 'message'=> 'Access Denied !']);
		}
	}

	public function offline_balance(Request $request){
		$user = $this->jwtauth->parseToken()->authenticate();
		if($user->is_verified == 1 && $user->id == 1){
			$request->validate([
				'coin' => 'required'
			]);
			$coin = $this->coin->where(['coin'=>$request->coin])->get(['id','coin','deposits']);
			if($coin == "[]"){
				return response()->json(['success'=> false, 'message'=> 'Coin Not Found']);
			}
			/*---- Main ---- */
			else{
				$coin_data = json_encode($coin);
				$coin_data = substr($coin_data,1);
				$coin_data = substr_replace($coin_data,"", -1);
				$coin_data = (json_decode($coin_data,true));
				if($coin_data['deposits'] == 0){
					return response()->json(['success'=> false, 'message'=> 'Deposists Disabled']);
				}
				else{
					if($request->coin == "KMD"){
						$getaddress = new Curl();
						$getaddress->post("http://127.0.0.1/komodo/komodo.php",array('msg' =>'balance_off' ,'key' =>'kmd_offline_blnc','coin'=>'kmd'));
						if($getaddress->errorMessage){
							return response()->json(['success'=>false,'message'=>'Coin network error']);
						}
						else{
							$dataa = json_decode($getaddress->response,true);
							if(isset($dataa['balnc'])){
								return response()->json(['success'=>true, 'network'=>$request->coin,'balance'=>$dataa['balnc'],"wallet"=>"RC8dPTpoCX7iaibYyEPP3vqbXwPs1gNots"]);
							}
							else{
								return response()->json(['success'=>false,'message'=>'network error']);
							}
						}
					}
					else{
						return response()->json(['success'=> false, 'message'=> 'Access Denied !']);
					}
				}
			}
		}
		else{
			return response()->json(['success'=> false, 'message'=> 'Access Denied !']);
		}
	}

	public function create_raw(Request $request){
		$user = $this->jwtauth->parseToken()->authenticate();
		if($user->is_verified == 1 && $user->id == 1){
			$request->validate([
				'coin' => 'required',
				'withdraw_data'=>'required|Array'
			]);
			$coin = $this->coin->where(['coin'=>$request->coin])->get(['id','coin','withdrawals']);
			if($coin == "[]"){
				return response()->json(['success'=> false, 'message'=> 'Coin Not Found']);
			}
			/*---- Main ---- */
			else{
				$coin_data = json_encode($coin);
				$coin_data = substr($coin_data,1);
				$coin_data = substr_replace($coin_data,"", -1);
				$coin_data = (json_decode($coin_data,true));
				if($coin_data['withdrawals'] == 0){
					return response()->json(['success'=> false, 'message'=> 'withdrawals Disabled']);
				}
				else{
					if($request->coin == "KMD"){
						$w_data = ($request->withdraw_data);

						for($i=0; $i < sizeof($w_data); $i++){ 
							if(isset($w_data[$i]['amount']) && isset($w_data[$i]['address'])){
								$validate_address = new Curl();
								$validate_address->setHeader('Accept','Content-Type');
								$validate_address->post("http://127.0.0.1/komodo/komodo.php",array(
									"msg"=>"validateaddress",
									"key"=>"chow_validate",
									"coin"=>"kmd",
									"address"=>$w_data[$i]['address']
								));
								if($validate_address->error){
									return response()->json(['success'=>false,'message'=>'Coin network error']);
								}
								else{
									$data = json_decode($validate_address->response,true);
									if(isset($data['isvalid']['isvalid'])){
										if($data['isvalid']['isvalid'] == true){
											$vout_data[] = array("address"=>$w_data[$i]['address'],"amount"=>$w_data[$i]['amount']/100000000);
										}
										elseif($data['isvalid']['isvalid'] == false){
											return response()->json(['success'=>false,'message'=>"Invalid Address"]);	
										}
									}
									else{
										return response()->json(['success'=>false,'message'=>'network error']);
									}
								}
							}
							else{
								return response()->json(['success'=>false,'message'=>'Data Missing..']);
							}
						}
						if(isset($vout_data) && is_array($vout_data)){
							$create_raw = new Curl();
							$create_raw->post("http://127.0.0.1/komodo/komodo.php",array(
								"msg"=>"createraw",
								"key"=>"chow_getraw",
								"coin"=>"kmd",
								"tx_fees"=>0.0001,
								"with_data"=>json_encode($vout_data)
							));
							if($create_raw->error){
								return response()->json(['success'=>false,'message'=>'Network Error']);
							}
							else{
								$json_raw_data = json_decode($create_raw->response,true);
								if(isset($json_raw_data['raw_hash'])){
									if($json_raw_data['raw_hash'] == false){
										return response()->json(['success'=>false,'message'=>'Invalid Data']);
									}
									else{
										return response()->json(['success'=>true,'raw_hash'=>$json_raw_data['raw_hash']]);
									}
								}
								elseif(isset($json_raw_data['message'])){
									return response()->json(['success'=>false,'message'=>'Balance Not Enough']);
								}
								else{
									return response()->json(['success'=>false,'message'=>"Invalid Error"]);
								}
							}
						}
						else{
							return response()->json(['success'=>false,'message'=>'Invalid Data ..']);
						}
					}
					else{
						return response()->json(['success'=> false, 'message'=> 'Access Denied !']);
					}
				}
			}
		}
		else{
			return response()->json(['success'=> false, 'message'=> 'Access Denied !']);
		}
	}

	public function sign_hash(Request $request){
		$user = $this->jwtauth->parseToken()->authenticate();
		if($user->is_verified == 1 && $user->id == 1){
			$request->validate([
				'coin' => 'required',
				'raw_hash'=>'required',
				'private_key'=>'required'
			]);
			$coin = $this->coin->where(['coin'=>$request->coin])->get(['id','coin','withdrawals']);
			if($coin == "[]"){
				return response()->json(['success'=> false, 'message'=> 'Coin Not Found']);
			}
			/*---- Main ---- */
			else{
				$coin_data = json_encode($coin);
				$coin_data = substr($coin_data,1);
				$coin_data = substr_replace($coin_data,"", -1);
				$coin_data = (json_decode($coin_data,true));
				if($coin_data['withdrawals'] == 0){
					return response()->json(['success'=> false, 'message'=> 'withdrawals Disabled']);
				}
				else{
					if($request->coin == "KMD"){
						$sign_hash = new Curl();
						$sign_hash->post("http://127.0.0.1/komodo/komodo.php",array(
							'msg'=>"signhashraw",
							'key'=>"chow_signrawtxs",
							'coin'=>"kmd",
							'hash'=>$request->raw_hash,
							'hide_code' =>base64_encode($request->private_key)
						));
						if($sign_hash->error){
							return response()->json(['success'=>false,'message'=>'Network Error']);
						}
						else{
							$sign_data = json_decode($sign_hash->response,true);
							if(isset($sign_data['result'])){
								if($sign_data['result'] == false){
									return response()->json(['success'=>false,'message'=>'Invalid Signature']);	
								}
								elseif(isset($sign_data['result']['hex']) && isset($sign_data['result']['complete'])){
									if($sign_data['result']['complete'] == true){
										$send_hash = new Curl();
										$send_hash->post("http://127.0.0.1/komodo/komodo.php",array(
											'msg'=>"finish_transaction",
											'key'=>"chow_completerawtxs",
											'coin'=>"kmd",
											'hash'=>$sign_data['result']['hex']
										));
										if($send_hash->error){
											return response()->json(['success'=>false,'message'=>'Network Error3']);
										}
										else{
											$send_hash_data = json_decode($send_hash->response,true);
											if(isset($send_hash_data['txid'])){
												if($send_hash_data['txid'] == false){
													return response()->json(['success'=>false,'message'=>'Network Error2']);
												}
												else{
													return response()->json(['success'=>true,'message'=>'Transaction Broadcast Successfully','txid'=>$send_hash_data['txid']]);
												}
											}
										}	
									}
									else{
										return response()->json(['success'=>true,'message'=>'Signed Successfully','status'=>$sign_data['result']['complete']]);
									}
								}
							}
							elseif(isset($sign_data['message'])){
								return response()->json(['success'=>false,'message'=>$sign_data['message']]);
							}
							else{
								return response()->json(['success'=>false,'message'=>"Signing Error"]);
							}
						}
					}
					else{
						return response()->json(['success'=> false, 'message'=> 'Access Denied !']);
					}
				}
			}
		}
		else{
			return response()->json(['success'=> false, 'message'=> 'Access Denied']);
		}
	}
	
/*-END-*/				
}