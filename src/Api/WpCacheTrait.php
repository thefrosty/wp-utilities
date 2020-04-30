<?php declare(strict_types=1);

namespace TheFrosty\WpUtilities\Api;

/**
 * Trait WpCacheTrait
 *
 * @package TheFrosty\WpUtilities\Api
 */
trait WpCacheTrait
{

    use Hash;

    /**
     * Cache group value.
     *
     * @var string|null $group
     */
    private $group;

    /**
     * Get the cache group.
     */
    protected function getCacheGroup(): string
    {
        return $this->group ?? static::class;
    }

    /**
     * Optional. Set the cache group.
     *
     * @param string $group
     */
    protected function setCacheGroup(string $group): void
    {
        $this->group = $group;
    }

    /**
     * Retrieve object from cache.
     *
     * @param string $key The key under which to store the value.
     * @param string|null $group The group value appended to the $key.
     * @param bool $force Optional. Whether to force an update of the local cache from the persistent
     *                                cache. Default false.
     * @param bool $found Optional. Whether the key was found in the cache. Disambiguates a return of false,
     *                                a storable value. Passed by reference. Default null.
     *
     * @return mixed Cached object value.
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    protected function getCache(string $key, ?string $group = null, bool $force = false, ?bool &$found = null)
    {
        return \wp_cache_get($key, $group ?? $this->group, $force, $found);
    }

    /**
     * Sets a value in cache.
     *
     * @param string $key The key under which to store the value.
     * @param mixed $value The value to store.
     * @param string|null $group The group value appended to the $key.
     * @param int $expiration The expiration time, defaults to 0.
     *
     * @return bool Returns TRUE on success or FALSE on failure.
     */
    protected function setCache(string $key, $value, ?string $group = null, int $expiration = 0): bool
    {
        return \wp_cache_set($key, $value, $group ?? $this->group, $expiration);
    }

    /**
     * Deletes a value in cache.
     *
     * @param string $key The key under which the value is stored.
     * @param string|null $group The group value appended to the $key.
     *
     * @return bool Returns TRUE on success or FALSE on failure.
     */
    protected function deleteCache(string $key, ?string $group = null): bool
    {
        return \wp_cache_delete($key, $group ?? $this->getCacheGroup());
    }
}
