<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Plugin;

use ArrayIterator;
use IteratorAggregate;
use function spl_object_id;

/**
 * Class Init
 * @package TheFrosty\WpUtilities\Plugin
 */
final class Init implements IteratorAggregate
{
    /**
     * The hook providers that have been initiated, keyed by object ID (not class name), so two instances of the
     * same class (e.g. one license manager or updater per add-on plugin) each get their hooks.
     * @var array<int, true> $initiated
     */
    private array $initiated = [];

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
            // Registered objects stay referenced in $wp_hooks, so their IDs are never reused.
            $id = spl_object_id($wp_hook);
            if ($wp_hook instanceof WpHooksInterface && !isset($this->initiated[$id])) {
                $this->initiated[$id] = true;
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
        return array_find($this->wp_hooks, static fn($object): bool => $object::class === $class_name);
    }
}
