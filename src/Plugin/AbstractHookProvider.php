<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Plugin;

/**
 * Class AbstractHookProvider
 * @package TheFrosty\WpUtilities\Plugin
 * @see \TheFrosty\WpUtilities\Tests\Plugin\AbstractHookProviderTest
 */
abstract class AbstractHookProvider implements WpHooksInterface, PluginAwareInterface
{
    use HooksTrait;
    use PluginAwareTrait;

    /**
     * Registers hooks for the plugin.
     */
    abstract public function addHooks(): void;
}
