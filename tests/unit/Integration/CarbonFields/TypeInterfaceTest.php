<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\Integration\CarbonFields;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\UsesClass;
use TheFrosty\WpUtilities\Integration\CarbonFields\TypeInterface;
use TheFrosty\WpUtilities\Tests\Plugin\Framework\TestCase;

/**
 * Class TypeInterfaceTest
 * @package TheFrosty\WpUtilities\Tests\Integration\CarbonFields
 */
#[CoversNothing]
#[UsesClass(TypeInterface::class)]
#[Group('integration')]
class TypeInterfaceTest extends TestCase
{
    /**
     * Test that all constants have correct values.
     */
    public function testConstantValues(): void
    {
        $this->assertSame('comment_meta', TypeInterface::COMMENT_META);
        $this->assertSame('post_meta', TypeInterface::POST_META);
        $this->assertSame('term_meta', TypeInterface::TERM_META);
        $this->assertSame('theme_options', TypeInterface::THEME_OPTIONS);
        $this->assertSame('user_meta', TypeInterface::USER_META);
    }
}
