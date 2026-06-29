<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'dashboard_layout',
        'default_currency',
        'default_region',
        'items_per_page',
        'theme',
        'email_notifications',
        'alert_notifications',
        'custom_settings',
    ];

    protected function casts(): array
    {
        return [
            'items_per_page' => 'integer',
            'email_notifications' => 'boolean',
            'alert_notifications' => 'boolean',
            'custom_settings' => 'array',
        ];
    }

    /**
     * Get the user this preference belongs to.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
