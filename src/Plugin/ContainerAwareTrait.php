<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Plugin;

use BadMethodCallException;
use Psr\Container\ContainerInterface;
use function call_user_func_array;
use function is_callable;
use function sprintf;

/**
 * Container aware trait.
 * Holds a PSR-11 container (PHP-DI, Pimple via `Plugin\Container`, or any other implementation) and proxies
 * container entries as properties (`$this->entry`) and callable entries as methods (`$this->entry(...$args)`).
 * Note: to store a *callable* (rather than a factory) use `DI\value($callable)` with PHP-DI or
 * `$pimple->protect($callable)` with Pimple; otherwise both containers call the closure to build the entry.
 * Container implementation courtesy of Slim 3.
 * @package TheFrosty\WpUtilities\Plugin
 */
trait ContainerAwareTrait
{

    /**
     * Container instance.
     * @var ContainerInterface|null
     */
    private ?ContainerInterface $container = null;

    /**
     * Proxy access to container entries.
     * @param string $name Entry ID.
     * @return mixed The entry, or `null` when there is no container.
     * @throws \Psr\Container\NotFoundExceptionInterface When the container has no entry for `$name`.
     */
    public function __get(string $name): mixed
    {
        return $this->container?->get($name);
    }

    /**
     * Whether a container entry exists.
     * @param string $name Entry ID.
     * @return bool
     */
    public function __isset(string $name): bool
    {
        return $this->container?->has($name) === true;
    }

    /**
     * Calling a non-existent method on the class checks to see if there's an
     * entry in the container that is callable and if so, calls it.
     * @param string $method Method name (container entry ID).
     * @param array $args Method arguments.
     * @return mixed
     * @throws BadMethodCallException When there's no callable container entry for `$method`.
     */
    public function __call(string $method, array $args): mixed
    {
        if ($this->container?->has($method)) {
            $object = $this->container->get($method);
            if (is_callable($object)) {
                return call_user_func_array($object, $args);
            }
        }

        throw new BadMethodCallException(
            sprintf('Call to undefined method %s::%s() (no callable container entry).', static::class, $method)
        );
    }

    /**
     * Enable access to the DI container by plugin consumers.
     * @return ContainerInterface|null
     */
    public function getContainer(): ?ContainerInterface
    {
        return $this->container;
    }

    /**
     * Set the container.
     * @param ContainerInterface|null $container Dependency injection container.
     * @return ContainerAwareTrait|AbstractContainerProvider|Plugin
     */
    public function setContainer(?ContainerInterface $container = null): self
    {
        $this->container = $container;

        return $this;
    }
}
