<?php declare(strict_types=1);

namespace TheFrosty\WpUtilities\Plugin;

/**
 * Main plugin class.
 * @package TheFrosty\WpUtilities\Plugin
 */
class Plugin extends AbstractPlugin
{
    use ContainerAwareTrait;

    public const TAG = 'thefrosty/wp_utilities';
}
