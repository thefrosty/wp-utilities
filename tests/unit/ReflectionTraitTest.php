<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests;

use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\Attributes\Group;
use ReflectionMethod;
use ReflectionObject;
use stdClass;
use TheFrosty\WpUtilities\ReflectionTrait;
use TheFrosty\WpUtilities\Tests\Plugin\Framework\TestCase;

/**
 * Class ReflectionTraitTest
 * @package TheFrosty\WpUtilities\Tests
 */
#[CoversTrait(ReflectionTrait::class)]
#[Group('utils')]
class ReflectionTraitTest extends TestCase
{
    public function testReturnsACachedReflectionObjectPerClass(): void
    {
        $host = new class {
            use ReflectionTrait;
        };
        $method = new ReflectionMethod($host, 'getReflection');

        $first = $method->invoke($host, new stdClass());
        $this->assertInstanceOf(ReflectionObject::class, $first);
        $this->assertSame(stdClass::class, $first->getName());
        $this->assertSame($first, $method->invoke($host, new stdClass()));
        $this->assertSame(PlainObject::class, $method->invoke($host, new PlainObject())->getName());
    }
}
