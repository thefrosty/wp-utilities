<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Api;

use function wp_cache_delete;
use function wp_cache_get;
use function wp_cache_set;

/**
 * Trait WpCacheTrait
 * @package TheFrosty\WpUtilities\Api
 */
trait WpCacheTrait
{
    use Hash;

    /**
     * Cache key value.
     * @var string|null $queryCacheKey
     */
    private ?string $queryCacheKey = null;

    /**
     * Cache group value.
     * @var string|null $queryCacheGroup
     */
    private ?string $queryCacheGroup = null;

    /**
     * Get the cache key for the current query.
     * With this value, you should be able to delete the cache for a specific query (if needed).
     * @return string|null
     */
    public function getQueryCacheKey(): ?string
    {
        return $this->queryCacheKey;
    }

    /**
     * Set the cache key for the current query.
     * @param string $queryCacheKey
     * @return string
     */
    public function setQueryCacheKey(string $queryCacheKey): string
    {
        $this->queryCacheKey = $queryCacheKey;

        return $this->queryCacheKey;
    }

    /**
     * Get the cache group the current query.
     */
    public function getCacheGroup(): string
    {
        return $this->queryCacheGroup ?? static::class;
    }

    /**
     * Optional. Set the cache group the current query.
     * @param string $group
     */
    public function setCacheGroup(string $group): void
    {
        $this->queryCacheGroup = $group;
    }

    /**
     * Retrieve an object from cache.
     * @param string $key The key under which to store the value.
     * @param string|null $group The group value appended to the $key.
     * @param bool $force Optional. Whether to force an update of the local cache from the persistent cache. Default
     *     false.
     * @param bool $found Optional. Whether the key was found in the cache. Disambiguate a return of false, a storable
     *     value. Passed by reference. Default null.
     * @return mixed Cached object value.
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function getCache(string $key, ?string $group = null, bool $force = false, ?bool &$found = null): mixed
    {
        return wp_cache_get($key, $group ?? $this->getCacheGroup(), $force, $found);
    }

    /**
     * Sets a value in cache.
     * @param string $key The key under which to store the value.
     * @param mixed $value The value to store.
     * @param string|null $group The group value appended to the $key.
     * @param int $expiration The expiration time, defaults to 0.
     * @return bool Returns TRUE on success or FALSE on failure.
     */
    public function setCache(string $key, mixed $value, ?string $group = null, int $expiration = 0): bool
    {
        return wp_cache_set($key, $value, $group ?? $this->getCacheGroup(), $expiration);
    }

    /**
     * Deletes a value in cache.
     * @param string $key The key under which the value is stored.
     * @param string|null $group The group value appended to the $key.
     * @return bool Returns TRUE on success or FALSE on failure.
     */
    public function deleteCache(string $key, ?string $group = null): bool
    {
        return wp_cache_delete($key, $group ?? $this->getCacheGroup());
    }
}
