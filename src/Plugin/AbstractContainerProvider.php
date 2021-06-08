<?php declare(strict_types=1);

namespace TheFrosty\WpUtilities\Plugin;

/**
 * Class AbstractContainerProvider
 * @package TheFrosty\WpUtilities\Plugin
 */
abstract class AbstractContainerProvider implements WpHooksInterface, PluginAwareInterface
{
    use ContainerAwareTrait, HooksTrait, PluginAwareTrait;

    /**
     * AbstractContainerProvider constructor.
     * @param Container|null $container Set the container, or use `$this->setContainer($container)`.
     */
    public function __construct(?Container $container = null)
    {
        if ($container) {
            $this->setContainer($container);
        }
    }

    /**
     * Registers hooks for the plugin.
     */
    abstract public function addHooks(): void;
}
