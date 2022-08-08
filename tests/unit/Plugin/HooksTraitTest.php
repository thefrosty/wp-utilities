<?php declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\Plugin;

use TheFrosty\WpUtilities\Tests\Plugin\Framework\Mock\HookProvider;
use TheFrosty\WpUtilities\Tests\Plugin\Framework\TestCase;

/**
 * Class HooksTraitTest
 * @package TheFrosty\WpUtilities\Test\Plugin
 */
class HooksTraitTest extends TestCase
{

    /**
     * Test registerFilters().
     */
    public function testRegisterFilters(): void
    {
        $provider = $this->getMockProvider(HookProvider::class);
        $provider->expects($this->exactly(1))
                 ->method(self::METHOD_ADD_FILTER)
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
     * Test registerActions().
     */
    public function testRegisterActions(): void
    {
        $provider = $this->getMockProvider(HookProvider::class);
        $provider->expects($this->exactly(1))
                 ->method(self::METHOD_ADD_FILTER)
                 ->will($this->returnCallback(function ($hook, $method, $priority, $arg_count) {
                     TestCase::assertSame('template_redirect', $hook);
                     TestCase::assertSame(10, $priority);
                     TestCase::assertSame(1, $arg_count);
                 }))
                 ->willReturn(true);

        /** HookProvider @var HookProvider $provider */
        $provider->registerActions();
    }
}
