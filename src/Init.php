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
     * Helper property to check whether the object has been initiated
     * or loaded. So this class can call the `initialize` method more
     * than once.
     *
     * @var string $property
     */
    private $property = 'initiated';

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
        foreach ( $this as $object ) {
            if ( $object instanceof WpHooksInterface &&
                 ! property_exists( $object, $this->property )
            ) {
                $object->addHooks();
                $object->{$this->property} = true;
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
