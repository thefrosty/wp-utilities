<?php declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\Plugin;

use PHPUnit\Framework\Attributes\CoversClass;
use TheFrosty\WpUtilities\Plugin\AbstractHookProvider;
use TheFrosty\WpUtilities\Plugin\PluginAwareInterface;
use TheFrosty\WpUtilities\Tests\Plugin\Framework\TestCase;
use TheFrosty\WpUtilities\Plugin\WpHooksInterface;

/**
 * Class AbstractHookProviderTest
 * @package TheFrosty\WpUtilities\Test\Plugin
 */
#[CoversClass(AbstractHookProvider::class)]
class AbstractHookProviderTest extends TestCase
{

    /**
     * Test AbstractHookProvider.
     */
    public function testImplementsInterfaces(): void
    {
        $provider = $this->getMockProviderForAbstractClass(AbstractHookProvider::class);
        $this->assertInstanceOf(WpHooksInterface::class, $provider);
        $this->assertInstanceOf(PluginAwareInterface::class, $provider);
    }

    /**
     * Test AbstractHookProvider traits.
     */
    public function testTraits(): void
    {
        $traits = \class_uses(AbstractHookProvider::class);
        $this->assertCount(2, $traits);
    }
}
