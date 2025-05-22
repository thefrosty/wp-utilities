<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Plugin;

/**
 * Class AbstractHookProvider
 * @package TheFrosty\WpUtilities\Plugin
 */
abstract class AbstractHookProvider implements WpHooksInterface, PluginAwareInterface
{
    use HooksTrait, PluginAwareTrait;

    /**
     * Registers hooks for the plugin.
     */
    abstract public function addHooks(): void;
}
