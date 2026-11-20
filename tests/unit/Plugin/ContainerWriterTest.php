<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\Plugin;

use DI\ContainerBuilder;
use LogicException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\UsesClass;
use Psr\Container\ContainerInterface;
use RuntimeException;
use stdClass;
use TheFrosty\WpUtilities\Plugin\Container;
use TheFrosty\WpUtilities\Plugin\ContainerWriter;
use TheFrosty\WpUtilities\Tests\Plugin\Framework\TestCase;

/**
 * Class ContainerWriterTest
 * @package TheFrosty\WpUtilities\Tests\Plugin
 */
#[CoversClass(ContainerWriter::class)]
#[UsesClass(Container::class)]
#[Group('plugin')]
class ContainerWriterTest extends TestCase
{
    public function testWritesSharedFactoriesAndValuesToPhpDiAndPimple(): void
    {
        foreach ([new ContainerBuilder()->build(), new Container()] as $container) {
            $this->assertTrue(ContainerWriter::isWritable($container));

            ContainerWriter::set($container, 'value', 'hello');
            ContainerWriter::set($container, 'service', static fn(): stdClass => new stdClass());
            ContainerWriter::set(
                $container,
                'needs.container',
                static fn(ContainerInterface $c): string => $c->get('value') . ' world'
            );

            $this->assertSame('hello', $container->get('value'));
            $this->assertInstanceOf(stdClass::class, $container->get('service'));
            $this->assertSame($container->get('service'), $container->get('service'), $container::class);
            $this->assertSame('hello world', $container->get('needs.container'), $container::class);
        }
    }

    public function testReadOnlyContainersAreRejected(): void
    {
        $container = $this->readOnlyContainer();
        $this->assertFalse(ContainerWriter::isWritable($container));

        $this->expectException(LogicException::class);
        ContainerWriter::set($container, 'value', 'hello');
    }

    private function readOnlyContainer(): ContainerInterface
    {
        return new class implements ContainerInterface {
            public function get(string $id): mixed
            {
                throw new RuntimeException($id);
            }

            public function has(string $id): bool
            {
                return false;
            }
        };
    }
}
