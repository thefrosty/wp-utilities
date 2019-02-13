<?php declare(strict_types=1);

namespace TheFrosty\WpUtilities\Plugin;

/**
 * Class PluginFactory
 * @package TheFrosty\WpUtilities\Plugin
 */
class PluginFactory
{
    const WP_ORG_UPDATE_CHECK = 'https://api.wordpress.org/plugins/update-check/';

    /**
     * Create a plugin instance.
     *
     * @param string $slug Plugin slug.
     * @param string|null $filename Optional. Absolute path to the main plugin file.
     *                         This should be passed if the calling file is not
     *                         the main plugin file.
     * @param bool $disable_check Disable the plugin from being checked against the WP.org plugin repo?
     * @return Plugin A Plugin object instance.
     */
    public static function create(string $slug, ?string $filename = '', bool $disable_check = false) : Plugin
    {
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
        if ($disable_check) {
            $plugin = self::disablePluginUpdateCheck($plugin);
        }

        return $plugin;
    }

    /**
     * Disable plugin update checks for the current plugin
     * @param Plugin $plugin
     * @return Plugin
     */
    private static function disablePluginUpdateCheck(Plugin $plugin) : Plugin
    {
        /**
         * Filters the arguments used in an HTTP request.
         * @link https://stackoverflow.com/a/39217270/558561
         * @param array $r An array of HTTP request arguments.
         * @param string $url The request URL.
         * @return array
         */
        \add_filter('http_request_args', function (array $args, string $url) use ($plugin) : array {
            if (\strpos($url, self::WP_ORG_UPDATE_CHECK) === 0) {
                $plugins = \json_decode($args['body']['plugins'], true);
                unset($plugins['plugins'][$plugin->getBasename()]);
                unset($plugins['active'][\array_search($plugin->getBasename(), $plugins['active'], true)]);
                $args['body']['plugins'] = \wp_json_encode($plugins);
            }
            return $args;
        });

        return $plugin;
    }

    /**
     * Set the Pimple\Container if it's available.
     *
     * @param Plugin $plugin
     * @return Plugin
     */
    private static function setContainer(Plugin $plugin) : Plugin
    {
        try {
            if (\class_exists('\Pimple\Container') &&
                \interface_exists('\Psr\Container\ContainerInterface')
            ) {
                $plugin->setContainer(new Container());
            }
        } catch (\InvalidArgumentException $exception) {
            if (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
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
