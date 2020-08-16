<?php declare(strict_types=1);

namespace TheFrosty\WpUtilities\Plugin;

/**
 * Interface WpHooksInterface
 * Provides a contract for classes that add WordPress hooks.
 * @package TheFrosty\WpUtilities\Plugin
 */
interface WpHooksInterface
{

    /**
     * Add class hooks.
     */
    public function addHooks(): void;
}
