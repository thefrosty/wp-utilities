<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Api\Shortcode\Handler;

use Exception;
use function add_action;
use function function_exists;
use function shortcode_ui_register_for_shortcode;

/**
 * Trait ShortcodeUiTrait
 * @package TheFrosty\WpUtilities\Api\Shortcode\Handler
 */
trait ShortcodeUiTrait
{

    /**
     * Required registerShortcodeUI method.
     */
    abstract public function registerShortcodeUI(): void;

    /**
     * Required pluginsLoaded method.
     */
    abstract public function pluginsLoaded(): void;

    /**
     * Register shortcode ui method `registerShortcodeUI()` on the
     * custom 'register_shortcode_ui' action hook.
     * @throws Exception
     */
    protected function addActionRegisterShortcodeUi(): void
    {
        if (!function_exists('shortcode_ui_register_for_shortcode')) {
            throw new Exception('Shortcake plugin needs to be activated to use ' . __METHOD__);
        }
        add_action('register_shortcode_ui', [$this, 'registerShortcodeUI']);
    }

    /**
     * Helper to register the Shortcode UI for shortcode callback using Shortcode UI.
     * This method will have a fatal error unless the Shortcake plugin is active. The dependencies' class
     * can be used to check for whether shortcake plugin is active.
     * @param string $shortcode_slug
     * @param array $shortcode_ui_args
     */
    protected function shortcodeUiRegisterShortcode(string $shortcode_slug, array $shortcode_ui_args): void
    {
        if (function_exists('shortcode_ui_register_for_shortcode')) {
            shortcode_ui_register_for_shortcode($shortcode_slug, $shortcode_ui_args);
        }
    }
}
