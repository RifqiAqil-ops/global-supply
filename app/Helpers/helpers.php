<?php

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

if (!function_exists('log_activity')) {
    /**
     * Log user or system activity to the database.
     *
     * @param string $action Action name (e.g. 'login', 'register', 'update_profile')
     * @param string|null $description Friendly description of the action
     * @param mixed $model Model instance or array to associate with this log
     * @param array|null $oldValues Array of previous values before change
     * @param array|null $newValues Array of new values after change
     * @return ActivityLog
     */
    function log_activity(string $action, ?string $description = null, $model = null, ?array $oldValues = null, ?array $newValues = null): ActivityLog
    {
        $userId = Auth::id();
        $modelType = null;
        $modelId = null;

        if ($model && is_object($model) && method_exists($model, 'getKey')) {
            $modelType = get_class($model);
            $modelId = $model->getKey();
        }

        return ActivityLog::create([
            'user_id' => $userId,
            'action' => $action,
            'model_type' => $modelType,
            'model_id' => $modelId,
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }
}

if (!function_exists('format_risk_score')) {
    /**
     * Format risk score with HTML badge classes based on standard intervals.
     *
     * @param float $score
     * @return array
     */
    function format_risk_score(float $score): array
    {
        if ($score <= 25) {
            return ['level' => 'Low', 'class' => 'bg-success', 'color' => '#198754'];
        } elseif ($score <= 50) {
            return ['level' => 'Medium', 'class' => 'bg-warning text-dark', 'color' => '#ffc107'];
        } elseif ($score <= 75) {
            return ['level' => 'High', 'class' => 'bg-danger', 'color' => '#dc3545'];
        } else {
            return ['level' => 'Critical', 'class' => 'bg-dark', 'color' => '#212529'];
        }
    }
}
