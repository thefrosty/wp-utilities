<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\Api\ValidationRules;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use TheFrosty\WpUtilities\Api\ValidationRules\IsArray;
use TheFrosty\WpUtilities\Tests\Plugin\Framework\TestCase;

/**
 * Class IsArrayTest
 * @package TheFrosty\WpUtilities\Tests\Api\ValidationRules
 */
#[CoversClass(IsArray::class)]
#[Group('api')]
class IsArrayTest extends TestCase
{
    public function testPassesOnlyForArraysAndNamesTheField(): void
    {
        $rule = new IsArray();

        $this->assertTrue($rule->passes('types', ['post'], []));
        $this->assertTrue($rule->passes('types', [], []));
        $this->assertFalse($rule->passes('types', 'post', []));
        $this->assertFalse($rule->passes('types', null, []));
        $this->assertSame('The types field must be an array.', $rule->message());
    }
}
