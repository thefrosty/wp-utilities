<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Utils;

/**
 * Interface SingletonInterface
 * @package TheFrosty\WpUtilities\Utils
 */
interface SingletonInterface
{
    /**
     * Get the instance of the class.
     * @return static
     */
    public static function getInstance(): static;
}
