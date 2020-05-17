<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ussd_user extends Model
{
    //
    protected $table = "ussd_user";
    protected $fillable = [
        'user_mobile',
    ];
}
