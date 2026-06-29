<?php

namespace App\Traits;

trait HasActivityLog
{
    /**
     * Boot trait to automatically record activity logs on model events.
     */
    protected static function bootHasActivityLog(): void
    {
        static::created(function ($model) {
            log_activity(
                'created',
                'Created ' . class_basename($model) . ' #' . $model->getKey(),
                $model,
                null,
                $model->toArray()
            );
        });

        static::updated(function ($model) {
            $dirty = $model->getDirty();
            $old = [];
            $new = [];

            foreach ($dirty as $key => $value) {
                $old[$key] = $model->getRawOriginal($key);
                $new[$key] = $value;
            }

            log_activity(
                'updated',
                'Updated ' . class_basename($model) . ' #' . $model->getKey(),
                $model,
                $old,
                $new
            );
        });

        static::deleted(function ($model) {
            log_activity(
                'deleted',
                'Deleted ' . class_basename($model) . ' #' . $model->getKey(),
                $model,
                $model->toArray(),
                null
            );
        });
    }
}
