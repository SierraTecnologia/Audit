<?php

namespace Audit\Models;

use Audit\Models\Base;

class Audits extends Base
{
    public $table = 'audits';

    public $primaryKey = 'id';

    /**
     * @var string[]
     *
     * @psalm-var array{0: string, 1: string}
     */
    public $fillable = [
        'token',
        'data',
    ];

    /**
     * @var array
     */
    public $rules = [];
}
