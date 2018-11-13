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
										$added = array('coin'=>'BTC','broker_id'=>2,'address'=>$txs['data']['transactions'][$i]['address'],'category'=>'receive','amount'=>$txs['data']['transactions'][$i]['amount'],'confirmations'=>$txs['data']['transactions'][$i]['confirmations'],'txid'=>$txs['data']['transactions'][$i]['txid'],'message'=>'None');
										$post_data[] = ($added);
									}	
								}
								if(isset($post_data) && is_array($post_data)){
									$add_block = $this->block_hash->where(['coin'=>'KMD'])->update(['blockhash' =>$txs['data']['lastblock']]);
									return response()->json(['success'=>true,'data'=>$post_data]);
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
}
