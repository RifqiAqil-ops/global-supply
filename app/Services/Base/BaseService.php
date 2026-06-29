<?php

namespace App\Services\Base;

abstract class BaseService
{
    /**
     * Base cache helper using configuration values.
     *
     * @param string $key Cache key name
     * @param string $configTtlKey Key name in gscrip.cache_ttl configuration
     * @param \Closure $callback Logic to execute if cache missed
     * @return mixed
     */
    protected function cacheRemember(string $key, string $configTtlKey, \Closure $callback): mixed
    {
        $ttl = config("gscrip.cache_ttl.{$configTtlKey}", 3600);
        return cache()->remember($key, $ttl, $callback);
    }
}
