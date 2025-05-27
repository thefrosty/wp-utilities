<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Plugin\Provider;

use TheFrosty\WpUtilities\Plugin\HooksTrait;
use TheFrosty\WpUtilities\Plugin\PluginAwareInterface;
use TheFrosty\WpUtilities\Plugin\PluginAwareTrait;
use TheFrosty\WpUtilities\Plugin\WpHooksInterface;
use function did_action;
use function dirname;
use function load_plugin_textdomain;

/**
 * Internationalization class.
 * @package TheFrosty\WpUtilities\Plugin
 */
class I18n implements PluginAwareInterface, WpHooksInterface
{
    use HooksTrait, PluginAwareTrait;

    /**
     * Register hooks.
     * Loads the text domain during the `init` action.
     */
    public function addHooks(): void
    {
        if (did_action('init')) {
            $this->loadTextDomain();

            return;
        }
        $this->addAction('init', [$this, 'loadTextDomain']);
    }

    /**
     * Load the text domain to localize the plugin.
     */
    protected function loadTextDomain(): void
    {
        load_plugin_textdomain(
            $this->getPlugin()->getSlug(),
            false,
            dirname($this->getPlugin()->getBasename()) . '/languages'
        );
    }
}
