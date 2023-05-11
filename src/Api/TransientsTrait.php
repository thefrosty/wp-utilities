<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Api;

use function md5;
use function set_transient;
use function strlen;
use function substr;

/**
 * Trait TransientsTrait
 * @package TheFrosty\WpUtilities\Api
 */
trait TransientsTrait
{

    use WpCacheTrait;

    /**
     * Transient key prefix.
     * @var string $prefix
     */
    private string $prefix = '_wp_utilities_';

    /**
     * Max allowable characters in the WP database.
     * @var int $wp_max_transient_chars
     */
    private int $wp_max_transient_chars = 45;

    /**
     * Gets the cached transient key.
     * @param string $input
     * @param string|null $key_prefix
     * @return string
     */
    public function getTransientKey(string $input, ?string $key_prefix = null): string
    {
        $key = $key_prefix ?? $this->prefix;

        return $this->setQueryCacheKey($key . substr(md5($input), 0, $this->wp_max_transient_chars - strlen($key)));
    }

    /**
     * @param string $transient Transient name. Expected to not be SQL-escaped. Must be 172 characters or fewer.
     * @param mixed $value Transient value. Must be serializable if non-scalar. Expected to not be SQL-escaped.
     * @param int $expiration Optional. Time until expiration in seconds. Default 0 (no expiration).
     * @return bool
     */
    public function setTransient(string $transient, mixed $value, int $expiration = 0): bool
    {
        return set_transient($transient, $value, $expiration);
    }
}
