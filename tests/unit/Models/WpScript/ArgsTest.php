<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\Models\WpScript;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use TheFrosty\WpUtilities\Models\WpScript\Args;
use TheFrosty\WpUtilities\Tests\Plugin\Framework\TestCase;

/**
 * Class ArgsTest
 * @package TheFrosty\WpUtilities\Tests\Models\WpScript
 */
#[CoversClass(Args::class)]
#[Group('models')]
class ArgsTest extends TestCase
{
    public function testDefaultsAndStrategy(): void
    {
        $args = new Args();

        $this->assertFalse($args->in_footer);
        $args->strategy = 'defer';
        $args->in_footer = true;

        $this->assertSame('defer', $args->strategy);
        $this->assertTrue($args->in_footer);
    }
}
