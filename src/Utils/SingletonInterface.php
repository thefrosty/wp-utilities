<?php declare(strict_types=1);

namespace TheFrosty\WpUtilities\Utils;

/**
 * Interface SingletonInterface
 * @package TheFrosty\WpUtilities\Utils
 */
interface SingletonInterface
{
    /**
     * @return SingletonInterface
     */
    public static function getInstance(): SingletonInterface;
}
