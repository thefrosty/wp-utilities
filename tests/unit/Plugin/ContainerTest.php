<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\Plugin;

use PHPUnit\Framework\Attributes\CoversClass;
use TheFrosty\WpUtilities\Plugin\Container;
use TheFrosty\WpUtilities\Tests\Plugin\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Class ContainerTest
 */
#[CoversClass(Container::class)]
class ContainerTest extends TestCase
{
    public function testGet(): void
    {
        $container = new Container();
        $container['test'] = 'value';
        $this->assertSame('value', $container->get('test'));
    }

    public function testHas(): void
    {
        $container = new Container();
        $container['test'] = 'value';
        $this->assertTrue($container->has('test'));
        $this->assertFalse($container->has('nonexistent'));
    }

    public function testGetNotFound(): void
    {
        $this->expectException(NotFoundExceptionInterface::class);
        $container = new Container();
        $container->get('nonexistent');
    }

    public function testGetContainerException(): void
    {
        $this->expectException(ContainerExceptionInterface::class);
        $container = new Container();
        $container->get('test');
    }
}
