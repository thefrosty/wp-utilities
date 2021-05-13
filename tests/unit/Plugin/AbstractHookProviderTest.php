<?php declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\Plugin;

use TheFrosty\WpUtilities\Plugin\AbstractHookProvider;
use TheFrosty\WpUtilities\Plugin\PluginAwareInterface;
use TheFrosty\WpUtilities\Tests\Plugin\Framework\TestCase;
use TheFrosty\WpUtilities\Plugin\WpHooksInterface;

/**
 * Class AbstractHookProviderTest
 * @package TheFrosty\WpUtilities\Test\Plugin
 */
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
}
