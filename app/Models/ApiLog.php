<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiLog extends Model
{
    /**
     * Disable updated_at — API logs are immutable.
     */
    const UPDATED_AT = null;

    protected $fillable = [
        'provider',
        'endpoint',
        'method',
        'status_code',
        'response_time',
        'request_params',
        'response_size',
        'error_message',
        'is_success',
        'called_at',
    ];

    protected function casts(): array
    {
        return [
            'status_code' => 'integer',
            'response_time' => 'decimal:2',
            'request_params' => 'array',
            'response_size' => 'integer',
            'is_success' => 'boolean',
            'called_at' => 'datetime',
        ];
    }
}
