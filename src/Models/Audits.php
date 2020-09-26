<?php

namespace Audit\Models;

use Audit\Models\Base;

class Audits extends Base
{
    
    public string $table = 'audits';

    public string $primaryKey = 'id';

    /**
     * @var string[]
     *
     * @psalm-var array{0: string, 1: string}
     */
    public array $fillable = [
        'token',
        'data',
    ];

    /**
     * @var array
     */
    public array $rules = [];
}
