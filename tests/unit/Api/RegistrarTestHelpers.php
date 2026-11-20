<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\Api;

use TheFrosty\WpUtilities\Plugin\Plugin;

/**
 * Shared constructor and accessors for the anonymous registrars in ObjectRegistrarManagerTest.
 * @package TheFrosty\WpUtilities\Tests\Api
 */
trait RegistrarTestHelpers
{
    public function __construct(Plugin $plugin, protected array $classes)
    {
        parent::__construct($plugin);
    }

    public function getOrder(): array
    {
        return $this->getInstantiatedOrder(static::class);
    }
}
