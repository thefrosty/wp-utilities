<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\Api\ValidationRules;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use stdClass;
use TheFrosty\WpUtilities\Api\ValidationRules\IsInstanceOf;
use TheFrosty\WpUtilities\Tests\Plugin\Framework\TestCase;

/**
 * Class IsInstanceOfTest
 * @package TheFrosty\WpUtilities\Tests\Api\ValidationRules
 */
#[CoversClass(IsInstanceOf::class)]
#[Group('api')]
class IsInstanceOfTest extends TestCase
{
    public function testPassesForAClassNameAndASubclass(): void
    {
        $rule = new IsInstanceOf([stdClass::class]);

        $this->assertTrue($rule->passes('item', stdClass::class, []));
        $this->assertTrue($rule->passes('item', IsInstanceOfChild::class, []));
    }

    public function testFailsWhenTheClassParameterIsMissingOrDoesNotMatch(): void
    {
        $this->assertFalse((new IsInstanceOf([]))->passes('item', stdClass::class, []));
        $this->assertFalse((new IsInstanceOf([self::class]))->passes('item', stdClass::class, []));
    }

    public function testMessageNamesTheFieldAndExpectedClass(): void
    {
        $rule = new IsInstanceOf([stdClass::class]);
        $rule->passes('item', stdClass::class, []);

        $this->assertSame('The item field must be an instance of stdClass.', $rule->message());
    }
}

class IsInstanceOfChild extends stdClass
{
}
