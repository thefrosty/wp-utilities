<?php declare(strict_types=1);

namespace TheFrosty\WpUtilities\Plugin;

/**
 * Class PluginFactory
 * @package TheFrosty\WpUtilities\Plugin
 */
class PluginFactory
{

    /** @var Plugin[] $instances */
    private static array $instances;

    /**
     * Get the plugin instance.
     * @param string $slug
     * @return Plugin
     */
    public static function getInstance(string $slug): Plugin
    {
        if (isset(self::$instances[$slug]) && self::$instances[$slug] instanceof Plugin) {
            return self::$instances[$slug];
        }

        return self::create($slug);
    }

    /**
     * Create a plugin instance.
     *
     * @param string $slug Plugin slug.
     * @param string|null $filename Optional. Absolute path to the main plugin file.
     *                         This should be passed if the calling file is not the main plugin file.
     * @return Plugin A Plugin object instance.
     */
    public static function create(string $slug, ?string $filename = ''): Plugin
    {
        if (isset(self::$instances[$slug]) && self::$instances[$slug] instanceof Plugin) {
            return self::$instances[$slug];
        }
        // Use the calling file as the main plugin file.
        if (empty($filename)) {
            // @codingStandardsIgnoreStart
            $backtrace = \debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 1);
            $filename = $backtrace[0]['file'];
            // @codingStandardsIgnoreEnd
        }

        $plugin = (new Plugin())->setInit(new Init())
            ->setBasename(\plugin_basename($filename))
            ->setDirectory(\plugin_dir_path($filename))
            ->setFile($filename)
            ->setSlug($slug)
            ->setUrl(\plugin_dir_url($filename));

        $plugin = self::setContainer($plugin);
        $plugin->setTemplateLoader(new TemplateLoader($plugin));
        self::$instances[$slug] = $plugin;

        return $plugin;
    }

    /**
     * Set the Pimple\Container if it's available.
     *
     * @param Plugin $plugin
     * @return Plugin
     */
    private static function setContainer(Plugin $plugin): Plugin
    {
        try {
            if (\class_exists('\Pimple\Container') && \interface_exists('\Psr\Container\ContainerInterface')) {
                $plugin->setContainer(new Container());
            }
        } catch (\InvalidArgumentException $exception) {
            if (\defined('WP_DEBUG_LOG') && \WP_DEBUG_LOG) {
                \error_log(
                    \sprintf(
                        '[DEBUG] The `Psr\Container\ContainerInterface` couldn\'t initiate. message: %s',
                        $exception->getMessage()
                    )
                );
            }
        }

        return $plugin;
    }
}
