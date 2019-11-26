<?php

namespace Audit\Models;

class Trackings extends Model
{
    
    public $table = 'trackings';

    public $primaryKey = 'id';

    public $fillable = [
        'token',
        'data',
    ];

    public static $rules = [];
}
