<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Api\Shortcode;

use TheFrosty\WpUtilities\Plugin\AbstractHookProvider;
use function add_shortcode;

/**
 * Class ShortcodeRegistrar
 * @package TheFrosty\WpUtilities\Api\Shortcode
 */
abstract class ShortcodeRegistrar extends AbstractHookProvider
{

    /**
     * ShortcodeInterface object.
     * @var ShortcodeInterface $shortcode
     */
    protected ShortcodeInterface $shortcode;

    /**
     * ShortcodeRegistrar constructor.
     * @param ShortcodeInterface $shortcode
     */
    public function __construct(ShortcodeInterface $shortcode)
    {
        $this->shortcode = $shortcode;
    }

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
