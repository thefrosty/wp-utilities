<?php declare(strict_types=1);

namespace TheFrosty\WpUtilities\Api;

/**
 * Trait TransientsTrait
 *
 * @package TheFrosty\WpUtilities\Api
 */
trait TransientsTrait
{

    /**
     * Transient key prefix.
     *
     * @var string $prefix
     */
    private $prefix = '_wp_utilities_';

    /**
     * Max allowable characters in the WP database.
     *
     * @var int $wp_max_transient_chars
     */
    private $wp_max_transient_chars = 45;

    /**
     * Get's the cached transient key.
     *
     * @param string $input
     * @param string|null $key_prefix
     *
     * @return string
     */
    protected function getTransientKey(string $input, ?string $key_prefix = null): string
    {
        $key = $key_prefix ?? $this->prefix;

        return $key . \substr(\md5($input), 0, $this->wp_max_transient_chars - \strlen($key));
    }

    /**
     * @param string $transient Transient name. Expected to not be SQL-escaped.
     *  Must be 172 characters or fewer in length.
     * @param mixed $value Transient value. Must be serializable if non-scalar.
     *  Expected to not be SQL-escaped.
     * @param int $expiration Optional. Time until expiration in seconds. Default 0 (no expiration).
     * @return bool
     * phpcs:disable Inpsyde.CodeQuality.ArgumentTypeDeclaration.NoArgumentType
     */
    protected function setTransient(string $transient, $value, int $expiration = 0): bool
    {
        return \set_transient($transient, $value, $expiration);
    }
}
