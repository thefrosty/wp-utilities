<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Plugin;

use ArrayAccess;
use DI\Container as DiContainer;
use LogicException;
use Psr\Container\ContainerInterface;
use function sprintf;

/**
 * Class ContainerWriter
 * PSR-11 containers are read-only; this writes an entry using the implementation's own API.
 * @package TheFrosty\WpUtilities\Plugin
 */
final class ContainerWriter
{

    /**
     * Can entries be written to the container?
     * @param ContainerInterface $container
     * @return bool
     */
    public static function isWritable(ContainerInterface $container): bool
    {
        return $container instanceof DiContainer || $container instanceof ArrayAccess;
    }

    /**
     * Set a container entry. A `Closure` is registered as a shared factory in both PHP-DI and Pimple.
     * @param ContainerInterface $container
     * @param string $id
     * @param mixed $value
     * @throws LogicException When the container is read-only.
     */
    public static function set(ContainerInterface $container, string $id, mixed $value): void
    {
        if ($container instanceof DiContainer) {
            $container->set($id, $value);

            return;
        }

        if ($container instanceof ArrayAccess) {
            $container[$id] = $value;

            return;
        }

        throw new LogicException(sprintf('Unable to set "%s": %s is read-only.', $id, $container::class));
    }
}
