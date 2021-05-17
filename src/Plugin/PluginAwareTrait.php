<?php declare(strict_types=1);

namespace TheFrosty\WpUtilities\Plugin;

/**
 * Trait PluginAwareTrait
 * @package TheFrosty\WpUtilities\Plugin
 */
trait PluginAwareTrait
{
    /**
     * Main plugin instance.
     * @var PluginInterface $plugin
     */
    private PluginInterface $plugin;

    /**
     * Get the main plugin instance.
     * @return PluginInterface
     */
    public function getPlugin(): PluginInterface
    {
        return $this->plugin;
    }

    /**
     * Set the main plugin instance.
     *
     * @param PluginInterface $plugin Main plugin instance.
     * @return PluginInterface
     */
    public function setPlugin(PluginInterface $plugin): PluginInterface
    {
        $this->plugin = $plugin;

        return $plugin;
    }
}
