<?php declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\Plugin;

use TheFrosty\WpUtilities\Plugin\AbstractHookProvider;
use TheFrosty\WpUtilities\Plugin\PluginAwareInterface;
use TheFrosty\WpUtilities\Tests\Plugin\Framework\TestCase;
use TheFrosty\WpUtilities\Plugin\WpHooksInterface;

/**
 * Class AbstractHookProviderTest
 *
 * @package TheFrosty\WpUtilities\Test\Plugin
 */
class AbstractHookProviderTest extends TestCase
{

    /**
     * Test.
     */
    public function test_implements_interfaces()
    {
        $provider = $this->get_mock_provider();
        $this->assertInstanceOf(WpHooksInterface::class, $provider);
        $this->assertInstanceOf(PluginAwareInterface::class, $provider);
    }

    /**
     * Get's a mock `AbstractHookProvider`.
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function get_mock_provider()
    {
        return $this->getMockBuilder(AbstractHookProvider::class)->getMockForAbstractClass();
    }
}
