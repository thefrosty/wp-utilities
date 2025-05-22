<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Plugin;

use TheFrosty\WpUtilities\Utils\AbstractSingleton;

/**
 * Class AbstractSingletonProvider
 * @package TheFrosty\WpUtilities\Plugin
 */
abstract class AbstractSingletonProvider extends AbstractSingleton implements WpHooksInterface, PluginAwareInterface
{
    use HooksTrait, PluginAwareTrait;

    /**
     * Registers hooks for the plugin.
     */
    abstract public function addHooks(): void;
}
