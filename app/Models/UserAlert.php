<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'country_id',
        'alert_type',
        'threshold_value',
        'comparison_operator',
        'is_active',
        'last_triggered_at',
        'trigger_count',
    ];

    protected function casts(): array
    {
        return [
            'threshold_value' => 'decimal:4',
            'is_active' => 'boolean',
            'last_triggered_at' => 'datetime',
            'trigger_count' => 'integer',
        ];
    }

    /**
     * Get the user who owns this alert.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the country this alert monitors.
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }
}
