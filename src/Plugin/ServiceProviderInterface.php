<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Plugin;

/**
 * Interface ServiceProviderInterface
 * A container-agnostic replacement for `Pimple\ServiceProviderInterface`: a provider returns its container entries,
 * and `Plugin::addServiceProvider()` registers them in whichever container the plugin uses (PHP-DI or Pimple).
 * @package TheFrosty\WpUtilities\Plugin
 */
interface ServiceProviderInterface
{

    /**
     * Get the container entries to register.
     * Each value is either a raw value, or a `Closure` factory. Factories are called once (entries are shared) and
     * receive the container when the first parameter is type-hinted `Psr\Container\ContainerInterface`, e.g.
     * `static fn(ContainerInterface $container): Upgrade => new Upgrade($container)`.
     * @return array<string, mixed> Entry ID => value or factory.
     */
    public function getDefinitions(): array;
}
