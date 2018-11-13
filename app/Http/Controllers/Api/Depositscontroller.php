<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\User;
use App\Broker;
use App\Coin;
use App\Coin_address;
use App\Block_hash;
use App\Http\Requests;
use Tymon\JWTAuth\JWTAuth;
use App\Http\Controllers\Controller;
use Tymon\JWTAuth\Exceptions\JWTException;
use Curl\Curl;
use Validator;

class Depositscontroller extends Controller
{
	private $user;
	private $coin;
	private $coin_address;
	private $block_hash;
	private $jwtauth;

	public function __construct(User $user, JWTAuth $jwtauth, Coin_address $coin_address, Coin $coin, Block_hash $block_hash){
		$this->user = $user;
		$this->jwtauth = $jwtauth;
		$this->coin = $coin;
		$this->coin_address = $coin_address;
		$this->block_hash = $block_hash;
	}

	public function decoder_bhai($code){
		$first = base64_decode($code);
		$second = explode("_", $first);
		if(sizeof($second) == 3){
			$third = $second[0].$second[2];
			$fourth = base64_decode($third);
			$fifth = explode("_", $fourth);
			return $fifth[0];
		}
		else{
			return "wrong";
		}
	}

	/*-- Posting Function --*/
	public function posting($api_url,$key,$code,$data_json){
		$curl = new Curl();
		$curl->setBasicAuthentication($this->decoder_bhai($key),$this->decoder_bhai($code));
		$curl->setHeader('content-type', 'application/json');
		$curl->setHeader('Accept', 'application/json');
		$curl->setHeader('X-Requested-With', 'XMLHttpRequest');
		$curl->post($api_url,$data_json);
		$err = $curl->error;
		if (!empty($err)) {
		  return false;
		} else {
		  return $curl;
		}
	}

	public function deposits_controller_kmd(Request $request){
		$request->validate([
			'coin'=>'required',
			'api_key'=>'required',
		]);
		if($request->api_key == md5('e-kmd-321')){
			if($request->coin == 'KMD'){
				$block_hashe = $this->block_hash->where(['coin'=>'KMD'])->get(['blockhash']);
				$block_hashe = json_decode($block_hashe);
				if(isset($block_hashe[0]->blockhash)){
					$check = new Curl();
					$check->post("http://127.0.0.1/komodo/komodo.php",array('msg'=>'hash' ,'key'=>'chow_hashlist','hash'=>$block_hashe[0]->blockhash, 'coin'=>'kmd'));
					if($check->error){
						return response()->json(['success'=>false, 'message'=>$check->errorMessage]);
					}
					else{
						if($check->response == '[]'){
							return response()->json(['message'=>'Null']);
						}
						else{
							$txs = json_decode($check->response,true);
							if(isset($txs['data']['transactions'])){
								for($i=0; $i < sizeof($txs['data']['transactions']); $i++){
									if($txs['data']['transactions'][$i]['category'] == 'receive'){
										$added = array('coin'=>'KMD','address'=>$txs['data']['transactions'][$i]['address'],'category'=>'receive','amount'=>$txs['data']['transactions'][$i]['amount'],'confirmations'=>$txs['data']['transactions'][$i]['confirmations'],'txid'=>$txs['data']['transactions'][$i]['txid'],'message'=>'None');
										$post_data[] = ($added);
									}	
								}
								if(isset($post_data) && is_array($post_data)){
									$add_block = $this->block_hash->where(['coin'=>'KMD'])->update(['blockhash' =>$txs['data']['lastblock']]);
									$dep = $this->receive_deposits(1,'KMD',json_encode($post_data),md5('access_send_deposits'));
									return response()->json(['success'=>true,'data'=>$post_data,'return'=>$dep]);
								}
								else{
									$add_block = $this->block_hash->where(['coin'=>'KMD'])->update(['blockhash' =>$txs['data']['lastblock']]);
									return response()->json(["success"=>true,"result"=>$add_block]);
								}
							}	
						}
					}
				}
				else{
					return response()->json(['success'=>false,'message'=>'coin not found']);
				}
			}
		}
		else{
			return response()->json(['success'=>false,'message'=>' Request Rejected']);
		}
	}

	public function receive_deposits($coinid,$coin,$data_json,$func_key){
		if($func_key == md5('access_send_deposits')){
			$coin_exist = $this->coin->where(['id'=>$coinid,'coin'=>$coin])->get(['id','coin']);
			if($coin_exist == '[]'){
				return response()->json(['success'=>false,'message'=>'Coin Not Found']);
			}
			else{
				$coin_exist = json_decode($coin_exist);
				/*--Main Fx--*/
				$data_json_deposits = json_decode($data_json,true);
				require_once __DIR__.'/../../../../lost/.env';
				for($i=0; $i < sizeof($data_json_deposits); $i++){
					if(isset($data_json_deposits[$i]['coin']) && isset($data_json_deposits[$i]['address']) && isset($data_json_deposits[$i]['category']) && isset($data_json_deposits[$i]['amount']) && isset($data_json_deposits[$i]['txid']) && isset($data_json_deposits[$i]['confirmations']) && isset($data_json_deposits[$i]['message'])){
						$coin_address = $this->coin_address->where(['coin'=>$data_json_deposits[$i]['coin'],'address'=>$data_json_deposits[$i]['address'],'message'=>$data_json_deposits[$i]['message']])->get(['userid','username']);
						$coin_add = json_decode($coin_address);
						$deposit = $this->deposit->where(['broker_id'=>$data_json_deposits[$i]['broker_id'],'txid'=>$data_json_deposits[$i]['txid'],'address'=>$data_json_deposits[$i]['address']])->get(['txid']);
						if($coin_address == "[]"){
							$userid_d = 0;
							$username_d = 'NULL';
						}
						else{
							$userid_d = $coin_add[0]->userid;
							$username_d = $coin_add[0]->username;	
						}

						/*-- Insert Tx --*/
						return json_encode(array('coin_id'=>$coin_exist[0]->id,'coin'=>$data_json_deposits[$i]['coin'],'userid'=>$userid_d,'username'=>$username_d,'address'=>$data_json_deposits[$i]['address'],'category'=>'receive','amount'=>$data_json_deposits[$i]['amount'],'confirmations'=>$data_json_deposits[$i]['confirmations'],'txid'=>$data_json_deposits[$i]['txid'],'message'=>$data_json_deposits[$i]['message']));
						// if($deposit == "[]"){
						// 	$added = $this->deposit->create(['coin_id'=>$coin_exist[0]->id,'coin'=>$data_json_deposits[$i]['coin'],'broker_id'=>$data_json_deposits[$i]['broker_id'],'userid'=>$userid_d,'username'=>$username_d,'address'=>$data_json_deposits[$i]['address'],'category'=>'receive','amount'=>$data_json_deposits[$i]['amount'],'confirmations'=>$data_json_deposits[$i]['confirmations'],'txid'=>$data_json_deposits[$i]['txid'],'message'=>$data_json_deposits[$i]['message']]);
						// 	if (!$added) {
						// 		echo 'error';
						// 	}
						// 	else{
						// 			/** --- websend --- **/
						// 		$data_json =  json_encode([$added,'status'=>'new']);
						// 		echo $data_json;
						// 		$status =  $this->posting($api_url,$api_key,$broker_replacer[$broker_username],$data_json);
						// 		//print_r($status);
						// 		//echo $api_url;
						// 		if($status == false){
						// 			//print_r($status);
						// 			$update_status = $this->deposit->where(['coin_id'=>$coin_exist[0]->id,'broker_id'=>$data_json_deposits[$i]['broker_id'],'address'=>$data_json_deposits[$i]['address'],'category'=>'receive','txid'=>$data_json_deposits[$i]['txid']])->update(['status'=>-1]);
						// 		}
						// 		else{
						// 			//print_r($status);
						// 			$update_status = $this->deposit->where(['coin_id'=>$coin_exist[0]->id,'broker_id'=>$data_json_deposits[$i]['broker_id'],'address'=>$data_json_deposits[$i]['address'],'category'=>'receive','amount'=>$data_json_deposits[$i]['amount'],'txid'=>$data_json_deposits[$i]['txid']])->update(['status'=>1]);
						// 		}
						// 		/** --- websend --- **/
						// 	}
						// }
						/*-- Insert Tx End--*/

						/*-- Update Tx --*/
						// else{
						// 	$updated = $this->deposit->where(['coin_id'=>$coin_exist[0]->id,'broker_id'=>$data_json_deposits[$i]['broker_id'],'txid'=>$data_json_deposits[$i]['txid'],'address'=>$data_json_deposits[$i]['address']])->update(['confirmations'=>$data_json_deposits[$i]['confirmations']]);
						// 	if(!$updated){
						// 		echo "error";
						// 	}
						// 	else{	
						// 		/** --- websend --- **/
						// 		$data_json =  json_encode(['coin_name'=>$data_json_deposits[$i]['coin'],'txid'=>$data_json_deposits[$i]['txid'],'confirmations'=>$data_json_deposits[$i]['confirmations'],'address'=>$data_json_deposits[$i]['address'],'userid'=>$userid_d,'username'=>$username_d,'status'=>'update']);
						// 		echo $data_json;
						// 		$status =  $this->posting($api_url,$api_key,$broker_replacer[$broker_username],$data_json);
						// 		if($status == false){
						// 			//print_r($status);
						// 			$update_status = $this->deposit->where(['coin_id'=>$coin_exist[0]->id,'broker_id'=>$data_json_deposits[$i]['broker_id'],'txid'=>$data_json_deposits[$i]['txid'],'address'=>$data_json_deposits[$i]['address']])->update(['status_update'=>-1]);
						// 		}
						// 		else{
						// 			//print_r($status);
						// 			$update_status = $this->deposit->where(['coin_id'=>$coin_exist[0]->id,'broker_id'=>$data_json_deposits[$i]['broker_id'],'txid'=>$data_json_deposits[$i]['txid'],'address'=>$data_json_deposits[$i]['address']])->update(['status_update'=>1]);
						// 		}
						// 			/** --- websend --- **/
						// 	}
						// }
						/*-- Update Tx End--*/
					}
				}
				//return response()->json(['success'=>true,'message'=>'Thanks For Submitting']);
				/*--Main Fx End--*/
			}
		}
		else{
			return response()->json(['success'=>false,'message'=>'Access Denied']);
		}
	}
}
