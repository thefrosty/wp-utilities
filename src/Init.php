<?php

namespace TheFrosty\WP\Utils;

/**
 * Class Init
 *
 * @package TheFrosty\WP\Utils
 */
class Init implements \IteratorAggregate {

    /**
     * A container for objects that implement WpHooksInterface interface
     *
     * @var WpHooksInterface[]
     */
    public $plugin_components = [];

    /**
     * Adds an object to $container property
     *
     * @param WpHooksInterface $wp_hooks
     *
     * @return Init
     */
    public function add( WpHooksInterface $wp_hooks ): Init {
        $this->plugin_components[] = $wp_hooks;

        return $this;
    }

    /**
     * All the methods that need to be performed upon plugin initialization should
     * be done here.
     */
    public function initialize() {
        foreach ( $this as $container_object ) {
            if ( $container_object instanceof WpHooksInterface ) {
                $container_object->addHooks();
            }
        }
    }

    /**
     * Provides an iterator over the $container property
     *
     * @return \ArrayIterator
     */
    public function getIterator(): \ArrayIterator {
        return new \ArrayIterator( $this->plugin_components );
    }
}
