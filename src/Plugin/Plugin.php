<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Plugin;

use LogicException;
use Psr\Container\ContainerInterface;
use function sprintf;

/**
 * Main plugin class.
 * @package TheFrosty\WpUtilities\Plugin
 * @see \TheFrosty\WpUtilities\Tests\Plugin\PluginTest
 */
class Plugin extends AbstractPlugin
{
    use ContainerAwareTrait;

    public const string TAG = 'thefrosty/wp_utilities';

    /**
     * Register a service provider's entries in the plugin's container.
     * @param ServiceProviderInterface $provider
     * @return $this
     * @throws LogicException When the plugin has no container, or it is read-only.
     */
    public function addServiceProvider(ServiceProviderInterface $provider): self
    {
        $container = $this->getContainer();
        if (!$container instanceof ContainerInterface) {
            throw new LogicException(sprintf('The "%s" plugin has no container.', $this->getSlug()));
        }

        foreach ($provider->getDefinitions() as $id => $value) {
            ContainerWriter::set($container, (string)$id, $value);
        }

        return $this;
    }
}
