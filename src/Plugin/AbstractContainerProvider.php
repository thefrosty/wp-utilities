<?php declare(strict_types=1);

namespace TheFrosty\WpUtilities\Plugin;

use Psr\Container\ContainerInterface;

/**
 * Class AbstractContainerProvider
 * @package TheFrosty\WpUtilities\Plugin
 */
abstract class AbstractContainerProvider implements WpHooksInterface, PluginAwareInterface
{
    use ContainerAwareTrait, HooksTrait, PluginAwareTrait;

    /**
     * AbstractContainerProvider constructor.
     * @param ContainerInterface|null $container Set the container, or use `$this->setContainer($container)`.
     */
    public function __construct(?ContainerInterface $container = null)
    {
        $this->setContainer($container);
    }

    /**
     * Registers hooks for the plugin.
     */
    abstract public function addHooks(): void;
}
