<?php declare(strict_types=1);

namespace TheFrosty\WpUtilities\Plugin\Provider;

use TheFrosty\WpUtilities\Plugin\HooksTrait;
use TheFrosty\WpUtilities\Plugin\PluginAwareInterface;
use TheFrosty\WpUtilities\Plugin\PluginAwareTrait;
use TheFrosty\WpUtilities\Plugin\WpHooksInterface;

/**
 * Internationalization class.
 *
 * @package TheFrosty\WpUtilities\Plugin
 */
class I18n implements PluginAwareInterface, WpHooksInterface
{

    use HooksTrait, PluginAwareTrait;

    /**
     * Register hooks.
     *
     * Loads the text domain during the `plugins_loaded` action.
     */
    public function addHooks(): void
    {
        if (\did_action('plugins_loaded')) {
            $this->loadTextdomain();

            return;
        }
        $this->addAction('plugins_loaded', [$this, 'loadTextdomain']);
    }

    /**
     * Load the text domain to localize the plugin.
     */
    protected function loadTextdomain(): void
    {
        \load_plugin_textdomain(
            $this->getPlugin()->getSlug(),
            false,
            \dirname($this->getPlugin()->getBasename()) . '/languages'
        );
    }
}
