<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class daily_charging extends Model
{
    //
     protected $fillable = [
        'statusCode','timeStamp','externalTrxId','statusDetail','internalTrxId',
    ];
}
