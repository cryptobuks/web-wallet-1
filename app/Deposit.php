<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Deposit extends Model
{
    //
    protected $fillable = ['coin', 'coin_id', 'userid', 'username', 'address','message','category','amount','confirmations','txid','comment',
];
    
}