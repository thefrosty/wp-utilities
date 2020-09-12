<?php declare(strict_types=1);

namespace TheFrosty\WpUtilities\Plugin;

/**
 * Interface PluginAwareInterface
 * @package TheFrosty\WpUtilities\Plugin
 */
interface PluginAwareInterface
{

    /**
     * Set the main plugin instance.
     *
     * @param PluginInterface $plugin Main plugin instance.
     * @return PluginInterface
     */
    public function setPlugin(PluginInterface $plugin): PluginInterface;
}
