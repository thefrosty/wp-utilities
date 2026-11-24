<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Plugin;

use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use TheFrosty\WpUtilities\Utils\View;

/**
 * Class DefaultServiceProvider
 * The container entries most plugins need: the current HTTP request and a `View`.
 * Register it as-is, or extend it to add your own entries and view paths:
 *
 * ```php
 * $plugin->addServiceProvider(new DefaultServiceProvider());
 *
 * class ServiceProvider extends DefaultServiceProvider
 * {
 *     public function getDefinitions(): array
 *     {
 *         return [...parent::getDefinitions(), 'my.service' => static fn(): MyService => new MyService()];
 *     }
 *
 *     protected function getViewPaths(): array
 *     {
 *         return [dirname(__DIR__) . '/views/'];
 *     }
 * }
 * ```
 * @package TheFrosty\WpUtilities\Plugin
 */
class DefaultServiceProvider implements ServiceProviderInterface
{

    /**
     * Container ID of the current `Symfony\Component\HttpFoundation\Request` (also available as `Request::class`).
     */
    public const string REQUEST = 'request';

    /**
     * Container ID of the shared `TheFrosty\WpUtilities\Utils\View`, as used by `Viewable::getView()`.
     */
    public const string VIEW = 'wp_utilities.view';

    /**
     * Get the container entries to register.
     * Entries are shared, so the request is created from the globals once per container.
     * @return array<string, mixed>
     */
    public function getDefinitions(): array
    {
        return [
            self::REQUEST => static fn(): Request => Request::createFromGlobals(),
            // Same instance by class name, so PHP-DI autowiring injects the current request (not an empty one).
            Request::class => static fn(ContainerInterface $container): Request => $container->get(self::REQUEST),
            self::VIEW => fn(): View => $this->createView(),
        ];
    }

    /**
     * Directories the shared View loads templates from, in addition to the library's own `views/` directory.
     * Override to add your plugin's view paths; later paths take precedence (they're prepended).
     * @return string[]
     */
    protected function getViewPaths(): array
    {
        return [];
    }

    /**
     * Create the shared View with the view paths added.
     * @return View
     */
    protected function createView(): View
    {
        $view = new View();
        foreach ($this->getViewPaths() as $path) {
            $view->addPath($path);
        }

        return $view;
    }
}
