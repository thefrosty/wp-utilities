<?php declare(strict_types=1);

namespace TheFrosty\WpUtilities\Plugin;

use Psr\Container\ContainerInterface;
use Pimple\Container as Pimple;

/**
 * Container class.
 *
 * Extends Pimple to satisfy the ContainerInterface.
 *
 * @package TheFrosty\WpUtilities\Plugin
 */
class Container extends Pimple implements ContainerInterface
{

    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string $container_id Identifier of the entry to look for.
     * @return mixed Entry.
     */
    public function get($container_id)
    {
        return $this->offsetGet($container_id);
    }

    /**
     * Whether the container has an entry for the given identifier.
     *
     * @param string $container_id Identifier of the entry to look for.
     * @return bool
     */
    public function has($container_id) : bool
    {
        return $this->offsetExists($container_id);
    }
}
