<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class premium_charging extends Model
{
    //
    protected $fillable = [
        'statusCode','timeStamp','externalTrxId','statusDetail','internalTrxId',
    ];
}
