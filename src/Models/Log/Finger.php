<?php

namespace Audit\Models\Log;

use Audit\Models\Base;

class Finger extends Base
{

    /**
     * @var false
     */
    protected bool $organizationPerspective = false;

    protected $table = 'log_fingers';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'customer_id',
        'credit_card_id',
        'user_id',
    ];


    /**
     * @var string[][]
     *
     * @psalm-var array{customer_id: array{type: string, analyzer: string}, credit_card_id: array{type: string, analyzer: string}, user_id: array{type: string, analyzer: string}, score: array{type: string, analyzer: string}}
     */
    protected array $mappingProperties = array(

        'customer_id' => [
            'type' => 'integer',
            "analyzer" => "standard",
        ],
        'credit_card_id' => [
            'type' => 'integer',
            "analyzer" => "standard",
        ],
        'user_id' => [
            'type' => 'integer',
            "analyzer" => "standard",
        ],
        'score' => [
            'type' => 'float',
            "analyzer" => "standard",
        ],
    );


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     *
     * @psalm-return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Illuminate\Database\Eloquent\Model>
     */
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\Illuminate\Support\Facades\Config::get('sitec.core.models.user', \App\Models\User::class), 'user_id', 'id');
    }
}
