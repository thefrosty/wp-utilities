<?php declare(strict_types=1);

namespace TheFrosty\WpUtilities\Plugin;

use Pimple\Container as Pimple;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Container110 class.
 * Extends Pimple to satisfy the ContainerInterface v1.1.0
 * @ref https://github.com/php-fig/container/blob/1.1.0/src/ContainerInterface.php.
 * @package TheFrosty\WpUtilities\Plugin
 */
class Container110 extends Pimple implements ContainerInterface
{

    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @return mixed Entry.
     * @throws ContainerExceptionInterface Error while retrieving the entry.
     * @throws NotFoundExceptionInterface  No entry was found for **this** identifier.
     */
    public function get(string $id)
    {
        return $this->offsetGet($id);
    }

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     *
     * `has($id)` returning true does not mean that `get($id)` will not throw an exception.
     * It does however mean that `get($id)` will not throw a `NotFoundExceptionInterface`.
     *
     * @param string $id Identifier of the entry to look for.
     * @return bool
     */
    public function has(string $id)
    {
        return $this->offsetExists($id);
    }
}
