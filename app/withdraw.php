<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class withdraw extends Model
{
    //
    
    protected $fillable = [
        'user_id','withdraw_amount','quiz_type',
    ];
}
