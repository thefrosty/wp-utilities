<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Api\Shortcode;

use TheFrosty\WpUtilities\Plugin\AbstractHookProvider;

/**
 * Class AbstractRegistrar
 * @package TheFrosty\WpUtilities\Api\Shortcode
 */
abstract class AbstractRegistrar extends AbstractHookProvider
{

    /**
     * AbstractRegistrar constructor.
     * @param ShortcodeInterface $shortcode
     */
    public function __construct(protected ShortcodeInterface $shortcode)
    {
    }

    /**
     * Register the shortcode.
     */
    abstract public function addShortcode(): void;
}
