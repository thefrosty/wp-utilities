<?php declare(strict_types=1);

namespace TheFrosty\WpUtilities\Plugin;

/**
 * Class Init
 *
 * @package TheFrosty\WpUtilities\Plugin
 */
abstract class Init implements \IteratorAggregate
{

    /**
     * Helper property to check whether the object has been initiated
     * or loaded. So this class can call `initialize()` method more than once.
     */
    const PROPERTY = 'initiated';

    /**
     * A container for objects that implement WpHooksInterface.
     *
     * @var WpHooksInterface[] $wp_hooks
     */
    public $wp_hooks = [];

    /**
     * Adds an object to $wp_hooks property.
     *
     * @param WpHooksInterface $wp_hooks Hook provider.
     * @param PluginInterface $plugin PluginInterface for plugin awareness.
     *
     * @return PluginInterface
     */
    protected function register(
        WpHooksInterface $wp_hooks,
        PluginInterface $plugin
    ) : PluginInterface {
        $this->wp_hooks[] = $wp_hooks;

        if ($wp_hooks instanceof PluginAwareInterface) {
            $wp_hooks->setPlugin($plugin);
        }

        return $plugin;
    }

    /**
     * All the methods that need to be performed upon plugin initialization should
     * be done here.
     */
    public function initialize()
    {
        foreach ($this as $wp_hooks) {
            if ($wp_hooks instanceof WpHooksInterface && ! property_exists($wp_hooks, self::PROPERTY)) {
                $wp_hooks->{self::PROPERTY} = true;
                $wp_hooks->addHooks();
            }
        }
    }

    /**
     * Provides an iterator over the $wp_hooks property.
     *
     * @return \ArrayIterator
     */
    public function getIterator() : \ArrayIterator
    {
        return new \ArrayIterator($this->wp_hooks);
    }
}
