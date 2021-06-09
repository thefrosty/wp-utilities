<?php declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\Plugin;

use TheFrosty\WpUtilities\Plugin\AbstractContainerProvider;
use TheFrosty\WpUtilities\Plugin\PluginAwareInterface;
use TheFrosty\WpUtilities\Tests\Plugin\Framework\TestCase;
use TheFrosty\WpUtilities\Plugin\WpHooksInterface;

/**
 * Class AbstractContainerProviderTest
 * @package TheFrosty\WpUtilities\Test\Plugin
 */
class AbstractContainerProviderTest extends TestCase
{

    /**
     * Test AbstractContainerProvider interfaces.
     */
    public function testImplementsInterfaces(): void
    {
        $provider = $this->getMockProviderForAbstractClass(AbstractContainerProvider::class);
        $this->assertInstanceOf(WpHooksInterface::class, $provider);
        $this->assertInstanceOf(PluginAwareInterface::class, $provider);
    }

    /**
     * Test AbstractContainerProvider traits.
     */
    public function testTraits(): void
    {
        $traits = \class_uses(AbstractContainerProvider::class);
        $this->assertCount(3, $traits);
    }
}
