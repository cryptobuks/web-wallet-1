<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class User_verifications extends Model
{
    //
    public $timestamps = false;
    protected $table = 'user_verifications';
    protected $fillable = ['user_id','token',];
}