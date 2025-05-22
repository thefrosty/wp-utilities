<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Utils;

use RuntimeException;

/**
 * Class AbstractSingleton
 * @package TheFrosty\WpUtilities\Utils
 */
abstract class AbstractSingleton implements SingletonInterface
{
    /**
     * Array of `SingletonInterface` objects.
     * @var static[] $instances
     */
    private static array $instances = [];

    /**
     * Get the instance of the class.
     * @return static
     */
    public static function getInstance(): static
    {
        self::$instances[static::class] ??= new static();

        return self::$instances[static::class];
    }

    /**
     * Nobody should unserialize this instance.
     * @throws RuntimeException
     */
    public function __wakeup(): void
    {
        throw new RuntimeException(sprintf('Cannot unserialize %s', static::class));
    }

    /**
     * AbstractSingleton constructor.
     */
    final protected function __construct()
    {
    }

    /**
     * Clone magic method is private, nobody should clone this instance.
     */
    private function __clone()
    {
    }
}
