<?php declare(strict_types=1);

namespace TheFrosty\WpUtilities\Plugin;

use ArrayIterator;

/**
 * Class Init
 * @package TheFrosty\WpUtilities\Plugin
 */
final class Init implements \IteratorAggregate
{

    /**
     * Helper property to check whether the object has been initiated
     * or loaded. So this class can call `initialize()` method more than once.
     */
    private const PROPERTY = 'initiated';

    /**
     * A container for objects that implement WpHooksInterface.
     * @var WpHooksInterface[] $wp_hooks
     */
    private array $wp_hooks = [];

    /**
     * Adds an object to $wp_hooks property.
     * @param WpHooksInterface $wp_hooks Hook provider.
     * @param PluginInterface $plugin PluginInterface for plugin awareness.
     * @return PluginInterface
     */
    public function register(
        WpHooksInterface $wp_hooks,
        PluginInterface $plugin
    ): PluginInterface {
        $this->wp_hooks[] = $wp_hooks;

        if ($wp_hooks instanceof PluginAwareInterface) {
            $wp_hooks->setPlugin($plugin);
        }

        if ($wp_hooks instanceof HttpFoundationRequestInterface) {
            $wp_hooks->setRequest();
        }

        return $plugin;
    }

    /**
     * All the methods that need to be performed upon plugin initialization should
     * be done here.
     */
    public function initialize(): void
    {
        foreach ($this as $wp_hook) {
            if ($wp_hook instanceof WpHooksInterface && !\property_exists($wp_hook, self::PROPERTY)) {
                $wp_hook->{self::PROPERTY} = true;
                $wp_hook->addHooks();
            }
        }
    }

    /**
     * Provides an iterator over the $wp_hooks property.
     * @return ArrayIterator
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->wp_hooks);
    }

    /**
     * Gets the array of registered WpHooksInterface objects.
     * @return WpHooksInterface[]
     */
    public function getWpHooks(): array
    {
        return $this->wp_hooks;
    }

    /**
     * Return the instance of the hook.
     * @param string $class_name
     * @return WpHooksInterface|null
     */
    public function getWpHookObject(string $class_name): ?WpHooksInterface
    {
        $instance = null;
        $wp_hooks = $this->getWpHooks();
        \array_walk(
            $wp_hooks,
            static function (WpHooksInterface $object, int $key) use ($class_name, &$instance, &$wp_hooks): void {
                if (\get_class($object) === $class_name) {
                    $instance = $wp_hooks[$key];
                }
            }
        );

        return $instance;
    }
}
