<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class point_table_live extends Model
{
    //
     protected $fillable = [
        'user_id','point','live_contest_id',
    ];
}
