<?php declare(strict_types=1);

namespace TheFrosty\WpUtilities\WpAdmin;

use TheFrosty\WpUtilities\Plugin\AbstractHookProvider;
use TheFrosty\WpUtilities\Plugin\Plugin;
use function __;
use function apply_filters;
use function esc_html;
use function is_object;
use function sprintf;

/**
 * Class DisablePluginAutoUpdates
 * Turns off WordPress' plugin auto-updates for the current plugin: the background updater never installs an update
 * for it, and the Plugins screen shows a note instead of the "Enable auto-updates" toggle.
 *
 * Pairs with DisablePluginUpdateCheck for plugins that are updated another way (e.g. with Composer): that class
 * stops new WordPress.org update offers, while this one also keeps an offer that's already cached (for example from
 * before the site switched to Composer) from being installed automatically.
 * @package TheFrosty\WpUtilities\WpAdmin
 */
class DisablePluginAutoUpdates extends AbstractHookProvider
{

    /**
     * Filter the note shown in the Plugins screen's auto-updates column.
     * @param string $text The note (plain text, escaped on output).
     * @param \TheFrosty\WpUtilities\Plugin\PluginInterface $plugin The current plugin.
     */
    public const string TAG_SETTING_TEXT = Plugin::TAG . '/disable_plugin_auto_updates/setting_text';

    /**
     * Add class hooks.
     */
    public function addHooks(): void
    {
        $this->addFilter('auto_update_plugin', [$this, 'disableAutoUpdate'], 999, 2);
        $this->addFilter('plugin_auto_update_setting_html', [$this, 'autoUpdateSettingHtml'], 10, 2);
    }

    /**
     * Never auto-update the current plugin (the decision `WP_Automatic_Updater::should_update()` asks for).
     * @param mixed $update Whether to update (bool), or null to use the site's auto-updates setting.
     * @param mixed $item The update offer; `$item->plugin` is the plugin basename.
     * @return mixed Other plugins' values are passed through unchanged.
     */
    protected function disableAutoUpdate(mixed $update, mixed $item): mixed
    {
        if (is_object($item) && ($item->plugin ?? null) === $this->getPlugin()->getBasename()) {
            return false;
        }

        return $update;
    }

    /**
     * Replace the "Enable auto-updates" toggle on the Plugins screen for the current plugin.
     * @param string $html The auto-updates column HTML.
     * @param string $plugin_file Path to the plugin file relative to the plugins directory.
     * @return string
     */
    protected function autoUpdateSettingHtml(string $html, string $plugin_file): string
    {
        if ($plugin_file !== $this->getPlugin()->getBasename()) {
            return $html;
        }

        $text = apply_filters(
            self::TAG_SETTING_TEXT,
            __('Automatic updates are disabled for this plugin.', 'wp-utilities'),
            $this->getPlugin()
        );

        return sprintf('<span class="wp-utilities-auto-updates-disabled">%s</span>', esc_html((string)$text));
    }
}
