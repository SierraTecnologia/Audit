<?php

namespace Audit\Models;

use Audit\Models\Base;

class Audits extends Base
{
    
    public $table = 'audits';

    public $primaryKey = 'id';

    public $fillable = [
        'token',
        'data',
    ];

    public $rules = [];
}
