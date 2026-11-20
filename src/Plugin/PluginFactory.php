<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Plugin;

use ArrayAccess;
use DI\Container as DiContainer;
use DI\ContainerBuilder;
use Pimple\Container as PimpleContainer;
use Psr\Container\ContainerInterface;
use function class_exists;
use function debug_backtrace;
use function plugin_basename;
use function plugin_dir_path;
use function plugin_dir_url;
use const DEBUG_BACKTRACE_PROVIDE_OBJECT;

/**
 * Class PluginFactory
 * @package TheFrosty\WpUtilities\Plugin
 */
class PluginFactory
{

    /** @var Plugin[] $instances */
    private static array $instances = [];

    /**
     * Get the plugin instance.
     * @param string $slug
     * @return Plugin
     */
    public static function getInstance(string $slug): Plugin
    {
        return self::$instances[$slug] ?? self::create($slug);
    }

    /**
     * Create a plugin instance.
     * @param string $slug Plugin slug.
     * @param string|null $filename Optional. Absolute path to the main plugin file.
     *                         This should be passed if the calling file is not the main plugin file.
     * @param ContainerInterface|null $container Optional. Any PSR-11 container to use instead of the default.
     * @return Plugin A Plugin object instance.
     */
    public static function create(
        string $slug,
        ?string $filename = '',
        ?ContainerInterface $container = null
    ): Plugin {
        if (isset(self::$instances[$slug]) && self::$instances[$slug] instanceof Plugin) {
            return self::$instances[$slug];
        }

        // Use the calling file as the main plugin file.
        if (empty($filename)) {
            // @codingStandardsIgnoreStart
            $backtrace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 1);
            $filename = $backtrace[0]['file'];
            // @codingStandardsIgnoreEnd
        }

        $plugin = new Plugin()
            ->setInit(new Init())
            ->setBasename(plugin_basename($filename))
            ->setDirectory(plugin_dir_path($filename))
            ->setFile($filename)
            ->setSlug($slug)
            ->setUrl(plugin_dir_url($filename));

        $plugin = self::setContainer($plugin, $container);
        $plugin->setTemplateLoader(new TemplateLoader($plugin));
        self::$instances[$slug] = $plugin;

        return $plugin;
    }

    /**
     * Attach a PSR-11 container to the plugin and register the plugin in it under its slug.
     * Resolution order:
     *  1. The container passed to `create()` (any PSR-11 implementation: PHP-DI, Pimple, Symfony, …).
     *  2. Pimple, when `pimple/pimple` is installed (unchanged v3 behaviour, so existing plugins keep working).
     *  3. PHP-DI, when `php-di/php-di` is installed (autowiring enabled).
     *  4. No container.
     * @param Plugin $plugin
     * @param ContainerInterface|null $container
     * @return Plugin
     */
    private static function setContainer(Plugin $plugin, ?ContainerInterface $container = null): Plugin
    {
        $container ??= self::getDefaultContainer();
        if (!$container instanceof ContainerInterface) {
            return $plugin;
        }

        $plugin->setContainer($container);
        self::registerPlugin($container, $plugin);

        return $plugin;
    }

    /**
     * Build the default container for the installed package, if any.
     * @return ContainerInterface|null
     */
    private static function getDefaultContainer(): ?ContainerInterface
    {
        if (class_exists(PimpleContainer::class)) {
            return new Container();
        }

        if (class_exists(ContainerBuilder::class)) {
            return new ContainerBuilder()->useAutowiring(true)->build();
        }

        return null;
    }

    /**
     * PSR-11 is read-only, so registering the plugin depends on the implementation.
     * @param ContainerInterface $container
     * @param Plugin $plugin
     */
    private static function registerPlugin(ContainerInterface $container, Plugin $plugin): void
    {
        if ($container instanceof DiContainer) {
            $container->set($plugin->getSlug(), $plugin);

            return;
        }

        if ($container instanceof ArrayAccess) {
            $container[$plugin->getSlug()] = $plugin;
        }
    }
}
