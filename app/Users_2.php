<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Users_2 extends Model
{
	public $timestamps = false;
	protected $connection = 'mysql1';
	protected $table = 'users';
	protected $fillable = ['id','username','email','factor_enable',];
}
class User_coin_2 extends Model
{
	public $timestamps = false;
	protected $connection = 'mysql1';
	protected $table = 'coin';
	protected $fillable = ['id','name','symbol','withdraw_min','withdraw_max','custom_fee','confirmations',];
}
class User_balance_2 extends Model
{
	public $timestamps = false;
	protected $connection = 'mysql1';
	protected $table = 'balances';
	protected $fillable = ['username','userid','coin','balance',];
}