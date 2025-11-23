<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Api\Shortcode;

use function add_shortcode;

/**
 * Class ShortcodeRegistrar
 * @package TheFrosty\WpUtilities\Api\Shortcode
 */
class ShortcodeRegistrar extends AbstractRegistrar
{

    /**
     * Add class hooks.
     */
    public function addHooks(): void
    {
        $this->addAction('init', [$this, 'addShortcode']);
    }

    /**
     * Register the shortcode.
     */
    public function addShortcode(): void
    {
        add_shortcode($this->shortcode->getTag(), [$this->shortcode->getHandler(), 'handler']);
    }
}
