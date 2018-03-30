<?php declare(strict_types=1);

namespace TheFrosty\PhpUnit\WpUtilities;

use TheFrosty\PhpUnit\WpUtilities\Framework\Mock\HookProvider;
use TheFrosty\PhpUnit\WpUtilities\Framework\TestCase;

/**
 * Class HooksTraitTest
 *
 * @package TheFrosty\PhpUnit\WpUtilities
 */
class HooksTraitTest extends TestCase
{

    /**
     * Test filters_added().
     */
    public function test_filters_added()
    {
        $provider = $this->get_mock_provider();

        $provider->expects($this->exactly(1))
            ->method('addFilter')
            ->will($this->returnCallback(function ($hook, $method, $priority, $arg_count) {
                TestCase::assertSame('theTitle', $hook);
                TestCase::assertSame(10, $priority);
                TestCase::assertSame(1, $arg_count);
            }))
            ->willReturn(true);

        /** HookProvider @var HookProvider $provider */
        $provider->registerFilters();
    }

    /**
     * Test actions_added().
     */
    public function test_actions_added()
    {
        $provider = $this->get_mock_provider();

        $provider->expects($this->exactly(1))
            ->method('addFilter')
            ->will($this->returnCallback(function ($hook, $method, $priority, $arg_count) {
                TestCase::assertSame('template_redirect', $hook);
                TestCase::assertSame(10, $priority);
                TestCase::assertSame(1, $arg_count);
            }))
            ->willReturn(true);

        /** HookProvider @var HookProvider $provider */
        $provider->registerActions();
    }

    /**
     * Get's a mocked `HookProvider` object.
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function get_mock_provider()
    {
        return $this->getMockBuilder(HookProvider::class)->setMethods(['addFilter'])->getMock();
    }
}
