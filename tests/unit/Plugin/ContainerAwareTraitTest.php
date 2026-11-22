<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\Plugin;

use BadMethodCallException;
use DI\ContainerBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use stdClass;
use TheFrosty\WpUtilities\Plugin\Container;
use TheFrosty\WpUtilities\Plugin\ContainerAwareTrait;
use TheFrosty\WpUtilities\Tests\Plugin\Framework\TestCase;

use function DI\value;

/**
 * Class ContainerAwareTraitTest
 * Every proxy is exercised against both PHP-DI and the Pimple bridge (`Plugin\Container`).
 * @package TheFrosty\WpUtilities\Tests\Plugin
 */
#[CoversClass(Container::class)]
#[CoversTrait(ContainerAwareTrait::class)]
#[Group('plugin')]
class ContainerAwareTraitTest extends TestCase
{
    /**
     * Containers holding the same entries: a value, a shared service, a callable and a non-callable.
     * @return array<string, array{ContainerInterface}>
     */
    public static function containers(): array
    {
        $greet = static fn(string $name = 'world'): string => "hello {$name}";

        $pimple = new Container();
        $pimple['greeting'] = 'hello';
        $pimple['service'] = static fn(): stdClass => new stdClass();
        $pimple['greet'] = $pimple->protect($greet);

        $php_di = new ContainerBuilder()->addDefinitions([
            'greeting' => 'hello',
            'service' => static fn(): stdClass => new stdClass(),
            'greet' => value($greet),
        ])->build();

        return [
            'PHP-DI' => [$php_di],
            'Pimple' => [$pimple],
        ];
    }

    public function testSetGetAndClearTheContainer(): void
    {
        $subject = $this->subject();
        $this->assertNull($subject->getContainer());

        $container = new Container();
        $this->assertSame($subject, $subject->setContainer($container));
        $this->assertSame($container, $subject->getContainer());

        $subject->setContainer();
        $this->assertNull($subject->getContainer());
    }

    #[DataProvider('containers')]
    public function testGetProxiesContainerEntries(ContainerInterface $container): void
    {
        $subject = $this->subject($container);

        // Previously `__get()` returned `$container && $container->get()`, i.e. `true`, not the entry.
        $this->assertSame('hello', $subject->greeting);
        $this->assertInstanceOf(stdClass::class, $subject->service);
        $this->assertSame($subject->service, $subject->service, 'Services are shared.');
    }

    #[DataProvider('containers')]
    public function testGetThrowsForAMissingEntry(ContainerInterface $container): void
    {
        $subject = $this->subject($container);

        $this->expectException(NotFoundExceptionInterface::class);
        $subject->missingService;
    }

    public function testGetIsNullWithoutAContainer(): void
    {
        $this->assertNull($this->subject()->someService);
    }

    #[DataProvider('containers')]
    public function testIsset(ContainerInterface $container): void
    {
        $subject = $this->subject($container);

        $this->assertTrue(isset($subject->greeting));
        $this->assertFalse(isset($subject->missingService));
        $this->assertFalse(isset($this->subject()->greeting), 'No container.');
    }

    #[DataProvider('containers')]
    public function testCallInvokesCallableEntries(ContainerInterface $container): void
    {
        $subject = $this->subject($container);

        $this->assertSame('hello world', $subject->greet());
        $this->assertSame('hello Austin', $subject->greet('Austin'));
    }

    #[DataProvider('containers')]
    public function testCallThrowsForNonCallableOrMissingEntries(ContainerInterface $container): void
    {
        $subject = $this->subject($container);

        foreach (['greeting', 'missingService'] as $method) {
            try {
                $subject->$method();
                $this->fail("Expected {$method}() to throw.");
            } catch (BadMethodCallException $exception) {
                $this->assertStringContainsString("::{$method}()", $exception->getMessage());
            }
        }
    }

    public function testCallThrowsWithoutAContainer(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->subject()->someMethod();
    }

    private function subject(?ContainerInterface $container = null): object
    {
        $subject = new class {
            use ContainerAwareTrait;
        };

        return $subject->setContainer($container);
    }
}
