<?php declare(strict_types=1);

namespace TheFrosty\WpUtilities\Utils;

use Exception;

/**
 * Class AbstractSingleton
 * @package TheFrosty\WpUtilities\Utils
 */
abstract class AbstractSingleton implements SingletonInterface
{
    /**
     * Array of `SingletonInterface` objects.
     * @var SingletonInterface[] $instances
     */
    private static array $instances = [];

    /**
     * @return SingletonInterface
     */
    public static function getInstance(): SingletonInterface
    {
        self::$instances[static::class] = self::$instances[static::class] ?? new static();

        return self::$instances[static::class];
    }

    /**
     * Nobody should unserialize this instance.
     * @throws Exception
     */
    public function __wakeup()
    {
        throw new Exception(sprintf('Cannot unserialize %s', static::class));
    }

    /**
     * AbstractSingleton constructor.
     */
    protected function __construct()
    {
    }

    /**
     * Clone magic method is private, nobody should clone this instance.
     */
    private function __clone()
    {
    }
}
