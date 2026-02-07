<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\Plugin;

use PHPUnit\Framework\Attributes\CoversNothing;
use TheFrosty\WpUtilities\Plugin\PluginAwareInterface;
use TheFrosty\WpUtilities\Plugin\PluginInterface;
use TheFrosty\WpUtilities\Tests\Plugin\Framework\TestCase;

/**
 * Class PluginAwareInterfaceTest
 */
#[CoversNothing]
class PluginAwareInterfaceTest extends TestCase
{
    public function testPluginAwareInterface(): void
    {
        $this->assertTrue(interface_exists(PluginAwareInterface::class));
    }

    public function testSetPlugin(): void
    {
        $mockPlugin = $this->createMock(PluginInterface::class);
        $mockAware = $this->createMock(PluginAwareInterface::class);
        $mockAware->expects($this->once())
            ->method('setPlugin')
            ->with($mockPlugin);
        $mockAware->setPlugin($mockPlugin);
    }
}
