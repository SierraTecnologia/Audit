<?php
/**
 * Registro de Ocorrencias de Logs
 */

namespace Audit\Models\Log;

use Audit\Models\Base;

class Occurrence extends Base
{

    protected $organizationPerspective = true;

    protected $table = 'log_occurrences';                                                                                               
                                                                                                                                                                                                 
    public $errorMessage = null;                                                                                                                                                                 
                                                                                                                                                                                                 
    public $rules =  [                                                                                                                                                                                 
        'name' => 'required|name|max:255',                                                                                                                                    
        'slug' => 'required|slug|max:255',
        // Simbolo de 3 letras: Real (BRL), Bitcoin (BTC)                                                                                                                                
        'symbol' => 'required|slug|max:255',
        // Volume Transacionado usando a própria moeda
        'circulating_supply' => 'required',                                                                                                                                     
        'status' => 'required|min:0|max:1',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'status',
    ];
}