<?php declare(strict_types=1);

namespace TheFrosty\WpUtilities\Plugin;

use Psr\Container\ContainerInterface;

/**
 * Container aware trait.
 *
 * Container implementation courtesy of Slim 3.
 *
 * @package TheFrosty\WpUtilities\Plugin
 * @link https://github.com/slimphp/Slim/blob/e80b0f8b4d23e165783e8bf241b31c35272b0e28/Slim/App.php
 */
trait ContainerAwareTrait
{

    /**
     * Container.
     *
     * @var ContainerInterface
     */
    private $container;

    /**
     * Proxy access to container services.
     *
     * @param string $name Service name.
     * @return mixed
     * @inheritdoc
     */
    public function __get(string $name)
    {
        return $this->getContainer()->get($name);
    }

    /**
     * Whether a container service exists.
     *
     * @param string $name Service name.
     * @return bool
     */
    public function __isset($name) : bool
    {
        return $this->getContainer()->has($name);
    }

    /**
     * Calling a non-existent method on the class checks to see if there's an
     * item in the container that is callable and if so, calls it.
     *
     * @param string $method Method name.
     * @param array $args Method arguments.
     * @return mixed
     * @inheritdoc
     */
    public function __call(string $method, array $args)
    {
        if ($this->getContainer()->has($method)) {
            $object = $this->container->get($method);
            if (\is_callable($object)) {
                return \call_user_func_array($object, $args);
            }
        }

        return false;
    }

    /**
     * Enable access to the DI container by plugin consumers.
     *
     * @return ContainerInterface
     */
    public function getContainer() : ContainerInterface
    {
        return $this->container;
    }

    /**
     * Set the container.
     *
     * @param ContainerInterface $container Dependency injection container.
     * @return $this
     * @throws \InvalidArgumentException If the object is incorrect.
     */
    public function setContainer(ContainerInterface $container) : parent
    {
        if (! ($container instanceof ContainerInterface)) {
            throw new \InvalidArgumentException(
                sprintf('Expected a %s, got a %s.', ContainerInterface::class, get_class($container))
            );
        }

        $this->container = $container;

        return $this;
    }
}
