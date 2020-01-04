<?php
/**
 * Registra as Mensagens de Logs
 */

namespace Audit\Models\Actions\Log;

use Support\Models\Base;

class Message extends Base
{

    protected $organizationPerspective = false;

    public $table = 'log_messages';

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

    protected $mappingProperties = array(
        'customer_id' => [
          'type' => 'integer',
          "analyzer" => "standard",
        ],
    );
}