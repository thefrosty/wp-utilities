<?php declare(strict_types=1);

namespace TheFrosty\WpUtilities\WpAdmin;

use TheFrosty\WpUtilities\Plugin\AbstractHookProvider;

/**
 * Class AddPluginIcon
 * @package TheFrosty\WpUtilities\WpAdmin
 */
class AddPluginIcons extends AbstractHookProvider
{
    /**
     * Path to plugin icon. Often using `plugin_dir_url`.
     * Example: [
     *  'svg' => '<url>]/icon.svg',
     *  '1x' => '<url>]/icon-128x128.png|jpg',
     *  '2x' => '<url>]/icon-256x256.png|jpg'
     * ]
     * @var array $icons
     */
    private array $icons;

    /**
     * AddPluginIcon constructor.
     * @param array $icons
     */
    public function __construct(array $icons)
    {
        $this->icons = $icons;
    }

    /**
     * Add class hooks
     */
    public function addHooks(): void
    {
        if (!empty($this->icons)) {
            $this->addFilter('all_plugins', [$this, 'filterAllPlugins']);
        }
    }

    /**
     * Disable plugin update checks for the current plugin
     * @link https://gist.github.com/robincornett/1fe6045b1acc64a329460e5c6023853e
     * @param array $plugins
     * @return array
     */
    protected function filterAllPlugins(array $plugins): array
    {
        if (\array_key_exists($this->getPlugin()->getBasename(), $plugins)) {
            $icons = ['svg', '1x', '2x'];
            foreach ($icons as $key) {
                if (!\array_key_exists($key, $this->icons)) {
                    continue;
                }
                $plugins[$this->getPlugin()->getBasename()]['icons'][$key] = $this->icons[$key];
            }
        }

        return $plugins;
    }
}
