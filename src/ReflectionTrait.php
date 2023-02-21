<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities;

use ReflectionObject;
use function get_class;

/**
 * Class ReflectionTrait
 * @package BeachbodyDigital
 */
trait ReflectionTrait
{

    /**
     * Gets an instance of the ReflectionObject.
     * @param object $argument
     * @return ReflectionObject
     */
    protected function getReflection(object $argument): ReflectionObject
    {
        static $reflector;

        if (
            !isset($reflector[get_class($argument)]) ||
            !($reflector[get_class($argument)] instanceof ReflectionObject)
        ) {
            $reflector[get_class($argument)] = new ReflectionObject($argument);
        }

        return $reflector[get_class($argument)];
    }
}
