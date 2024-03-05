<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Api;

use function get_transient;
use function is_numeric;
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
     * Get the transient value.
     * @param string $transient Transient name.
     * @return mixed
     */
    public function getTransient(string $transient): mixed
    {
        return get_transient($transient);
    }

    /**
     * Set the transient value.
     * @param string $transient Transient name. Expected to not be SQL-escaped. Must be 172 characters or fewer.
     * @param mixed $value Transient value. Must be serializable if non-scalar. Expected to not be SQL-escaped.
     * @param int $expiration Optional. Time until expiration in seconds. Default 0 (no expiration).
     * @return bool
     */
    public function setTransient(string $transient, mixed $value, int $expiration = 0): bool
    {
        return set_transient($transient, $value, $expiration);
    }

    /**
     * Get the transient timeout value.
     * @param string $transient
     * @return int|null
     */
    public function getTransientTimeout(string $transient): ?int
    {
        global $wpdb;
        $timeout = $wpdb->get_col(
            "
SELECT option_value
FROM $wpdb->options
WHERE option_name
LIKE '%_transient_timeout_$transient%'"
        );
        return !isset($timeout[0]) || !is_numeric($timeout[0]) ? null : (int)$timeout[0];
    }
}
