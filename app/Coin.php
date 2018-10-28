<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Coin extends Model
{
    //
    protected $fillable = ['coin', 'coin_name', 'withdraw_fees', 'min_withdraw', 'message', 'is_fiat', 'is_auto', 'maker', 'taker', 'confirmations',
];
}