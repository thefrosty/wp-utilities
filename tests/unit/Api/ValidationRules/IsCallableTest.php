<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\Api\ValidationRules;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use TheFrosty\WpUtilities\Api\ValidationRules\IsCallable;
use TheFrosty\WpUtilities\Tests\Plugin\Framework\TestCase;

/**
 * Class IsCallableTest
 * @package TheFrosty\WpUtilities\Tests\Api\ValidationRules
 */
#[CoversClass(IsCallable::class)]
#[Group('api')]
class IsCallableTest extends TestCase
{
    public function testPassesOnlyForCallablesAndNamesTheField(): void
    {
        $rule = new IsCallable(['unused']);

        $this->assertTrue($rule->passes('sanitize_callback', 'sanitize_text_field', []));
        $this->assertTrue($rule->passes('sanitize_callback', static fn(): bool => true, []));
        $this->assertFalse($rule->passes('sanitize_callback', 'not_a_real_function_name', []));
        $this->assertFalse($rule->passes('sanitize_callback', 42, []));
        $this->assertSame('The sanitize_callback field must be callable.', $rule->message());
    }
}
