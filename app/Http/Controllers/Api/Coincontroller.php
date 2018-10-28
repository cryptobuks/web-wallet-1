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
		if($user->is_verified == 1 && $user->id == 1)){
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
							$coin_addresses = json_decode($coin_addresses);
					        return response()->json($coin_addresses);
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
		if($user->is_verified == 1 && $user->id == 1)){
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
		if($user->is_verified == 1 && $user->id == 1)){
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
	
/*-END-*/				
}