<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\Plugin;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use RuntimeException;
use TheFrosty\WpUtilities\Plugin\AbstractSingletonProvider;
use TheFrosty\WpUtilities\Plugin\PluginAwareInterface;
use TheFrosty\WpUtilities\Plugin\WpHooksInterface;
use TheFrosty\WpUtilities\Tests\Plugin\Framework\TestCase;

/**
 * Class AbstractSingletonProviderTest
 * @package TheFrosty\WpUtilities\Tests\Plugin
 */
#[CoversClass(AbstractSingletonProvider::class)]
#[Group('plugin')]
class AbstractSingletonProviderTest extends TestCase
{
    public function testGetInstanceReturnsTheSameHookProvider(): void
    {
        $instance = SingletonProviderFixture::getInstance();

        $this->assertInstanceOf(WpHooksInterface::class, $instance);
        $this->assertInstanceOf(PluginAwareInterface::class, $instance);
        $this->assertSame($instance, SingletonProviderFixture::getInstance());
    }

    public function testWakeupIsRejected(): void
    {
        $this->expectException(RuntimeException::class);
        SingletonProviderFixture::getInstance()->__wakeup();
    }
}

class SingletonProviderFixture extends AbstractSingletonProvider
{
    public function addHooks(): void
    {
    }
}
