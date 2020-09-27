<?php
/**
 * Registra as Mensagens de Logs
 */

namespace Audit\Models\Log;

use Audit\Models\Base;

class Message extends Base
{

    /**
     * @var false
     */
    protected bool $organizationPerspective = false;

    public $table = 'log_messages';

    /**
     * @var string[]
     *
     * @psalm-var array{order_params: string}
     */
    protected $casts = [
        'order_params' => 'json',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'order_params',
        'gateway_id',
        'is_error_system',
        'gateway_error_code',
        'is_block_for_insufficient_funds',
        'order_id',
        'customer_id',
        'user_id'
    ];

    /**
     * @var string[][]
     *
     * @psalm-var array{customer_id: array{type: string, analyzer: string}}
     */
    protected array $mappingProperties = array(
        'customer_id' => [
          'type' => 'integer',
          "analyzer" => "standard",
        ],
    );
}
