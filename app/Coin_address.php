<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Coin_address extends Model
{
    //
    protected $fillable = ['coin', 'coin_id', 'address', 'message', 'userid','username',];
}