<?php declare( strict_types=1 );

namespace TheFrosty\WP\Utils\Plugin;

use function { // WordPress functions
    add_filter, remove_filter, _wp_filter_build_unique_id
};
use function { // PHP functions
    call_user_func_array, array_slice, func_get_args
};

/**
 * Trait HooksTrait
 * Allows protected and private methods to be used as hook callbacks.
 * @package TheFrosty\WP\Utils\Plugin
 * @license MIT
 * @link https://github.com/johnpbloch/wordpress-dev/blob/master/src/Hooks.php
 */
trait HooksTrait {

    /**
     * Internal property to track closures attached to WordPress hooks.
     *
     * @var \Closure[]
     */
    protected $filter_map = [];

    /**
     * Add a WordPress filter.
     *
     * @param string $hook
     * @param callable $method
     * @param int $priority
     * @param int $arg_count
     * @return bool true
     */
    protected function addFilter( string $hook, callable $method, int $priority = 10, int $arg_count = 1 ) : bool {
        return add_filter( $hook, $this->map_filter( $this->get_wp_filter_id( $hook, $method, $priority ), $method, $arg_count ), $priority, $arg_count );
    }

    /**
     * Add a WordPress action.
     *
     * This is an alias of add_filter().
     *
     * @param string $hook
     * @param callable $method
     * @param int $priority
     * @param int $arg_count
     * @return bool true
     */
    protected function addAction( string $hook, callable $method, int $priority = 10, int $arg_count = 1 ) : bool {
        return $this->addFilter( $hook, $method, $priority, $arg_count );
    }

    /**
     * Remove a WordPress filter.
     *
     * @param string $hook
     * @param callable $method
     * @param int $priority
     * @return bool Whether the function existed before it was removed.
     */
    protected function removeFilter( string $hook, callable $method, int $priority = 10 ) : bool {
        return remove_filter( $hook, $this->map_filter( $this->get_wp_filter_id( $hook, $method, $priority ), $method ), $priority );
    }

    /**
     * Remove a WordPress action.
     *
     * This is an alias of remove_filter().
     *
     * @param string $hook
     * @param callable $method
     * @param int $priority
     * @return bool Whether the function is removed.
     */
    protected function removeAction( string $hook, callable $method, int $priority = 10 ) : bool {
        return $this->removeFilter( $hook, $method, $priority );
    }

    /**
     * Get a unique ID for a hook based on the internal method, hook, and priority.
     *
     * @param string $hook
     * @param callable $method
     * @param int $priority
     * @return bool|string
     */
    protected function get_wp_filter_id( string $hook, callable $method, int $priority ) {
        return _wp_filter_build_unique_id( $hook, $method, $priority );
    }

    /**
     * Map a filter to a closure that inherits the class' internal scope.
     *
     * This allows hooks to use protected and private methods.
     *
     * @param string $id
     * @param callable $method
     * @param int $arg_count
     * @return \Closure The callable actually attached to a WP hook
     */
    private function map_filter( string $id, callable $method, int $arg_count = 1 ) : \Closure {
        if ( empty( $this->filter_map[ $id ] ) ) {
            $this->filter_map[ $id ] = function () use ( $method, $arg_count ) {
                return call_user_func_array( $method, array_slice( func_get_args(), 0, $arg_count ) );
            };
        }

        return $this->filter_map[ $id ];
    }
}
