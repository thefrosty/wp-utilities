<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Api;

use TheFrosty\WpUtilities\Plugin\AbstractHookProvider;
use TheFrosty\WpUtilities\Plugin\Plugin;
use function array_walk;

/**
 * Class ObjectRegistrarManager
 * @package TheFrosty\WpUtilities\Api
 */
abstract class ObjectRegistrarManager extends AbstractHookProvider implements RegistrarInterface
{

    use Instantiate;

    /**
     * ObjectRegistrarManager constructor
     * @param Plugin $plugin
     */
    public function __construct(Plugin $plugin)
    {
        $this->setPlugin($plugin);
    }

    /**
     * Add class hooks.
     */
    public function addHooks(): void
    {
        $classes = $this->getObjectClasses();
        array_walk($classes, [$this, 'instantiateClasses']);
    }

    /**
     * Get all registered objects from our filter.
     * @return array
     */
    abstract public function getObjectClasses(): array;
}
