<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class point_table_premium extends Model

{
    protected $table ="point_table_premiums";
     protected $fillable = [
        'user_id','point',
    ];
}
