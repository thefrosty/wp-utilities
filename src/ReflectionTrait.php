<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities;

use ReflectionObject;

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
            !isset($reflector[$argument::class]) ||
            !($reflector[$argument::class] instanceof ReflectionObject)
        ) {
            $reflector[$argument::class] = new ReflectionObject($argument);
        }

        return $reflector[$argument::class];
    }
}
