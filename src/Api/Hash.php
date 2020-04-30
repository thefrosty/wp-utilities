<?php declare(strict_types=1);

namespace TheFrosty\WpUtilities\Api;

/**
 * Trait Hash
 *
 * @package TheFrosty\WpUtilities\Api
 */
trait Hash
{

    /**
     * Get a cache key.
     *
     * @param string $data
     *
     * @return string
     */
    protected function getHashedKey(string $data): string
    {
        return \hash('sha256', $data);
    }
}
