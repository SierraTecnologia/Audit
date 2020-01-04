<?php

namespace Audit\Models;

class Audits extends Model
{
    
    public $table = 'audits';

    public $primaryKey = 'id';

    public $fillable = [
        'token',
        'data',
    ];

    public $rules = [];
}
