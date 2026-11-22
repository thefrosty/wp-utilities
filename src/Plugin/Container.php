<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Plugin;

use Pimple\Container as Pimple;
use Psr\Container\ContainerInterface;

/**
 * Container class.
 * The Pimple bridge: extends `Pimple\Container` (keeping its `ArrayAccess` API) and implements PSR-11, so plugins
 * that still use Pimple work with code that type-hints `ContainerInterface`.
 * Requires `pimple/pimple`; `PluginFactory` only uses it when PHP-DI isn't installed (or when passed in explicitly).
 * @deprecated 4.0.0 Kept so v3 (Pimple) plugins can migrate; prefer PHP-DI. Planned for removal in 5.0.0.
 * @ref https://github.com/php-fig/container/blob/2.0.0/src/ContainerInterface.php
 * @package TheFrosty\WpUtilities\Plugin
 * @see \TheFrosty\WpUtilities\Tests\Plugin\ContainerTest
 */
class Container extends Pimple implements ContainerInterface
{

    /**
     * Finds an entry of the container by its identifier and returns it.
     * @param string $id Identifier of the entry to look for.
     * @return mixed Entry.
     * @throws \Psr\Container\NotFoundExceptionInterface No entry was found for **this** identifier
     *     (`Pimple\Exception\UnknownIdentifierException`).
     */
    public function get(string $id): mixed
    {
        return $this->offsetGet($id);
    }

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     * `has($id)` returning true does not mean that `get($id)` will not throw an exception.
     * It does however mean that `get($id)` will not throw a `NotFoundExceptionInterface`.
     * @param string $id Identifier of the entry to look for.
     * @return bool
     */
    public function has(string $id): bool
    {
        return $this->offsetExists($id);
    }
}
