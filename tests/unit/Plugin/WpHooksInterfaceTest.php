<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\Plugin;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use TheFrosty\WpUtilities\Plugin\WpHooksInterface;

/**
 * Class WpHooksInterfaceTest
 */
#[CoversNothing]
class WpHooksInterfaceTest extends TestCase
{
    public function testWpHooksInterface(): void
    {
        $this->assertTrue(interface_exists(WpHooksInterface::class));
    }

    public function testAddHooksMethod(): void
    {
        $this->assertTrue(
            method_exists(WpHooksInterface::class, 'addHooks'),
            "WpHooksInterface should have method addHooks"
        );
    }
}
