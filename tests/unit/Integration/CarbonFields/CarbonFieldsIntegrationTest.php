<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\Integration\CarbonFields;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use TheFrosty\WpUtilities\Integration\CarbonFields\CarbonFields;
use TheFrosty\WpUtilities\Integration\CarbonFields\FieldsInterface;
use TheFrosty\WpUtilities\Integration\CarbonFields\TypeInterface;
use TheFrosty\WpUtilities\Tests\Plugin\Framework\TestCase;

/**
 * Class CarbonFieldsIntegrationTest
 * @package TheFrosty\WpUtilities\Tests\Integration\CarbonFields
 */
#[CoversClass(CarbonFields::class)]
#[Group('integration')]
class CarbonFieldsIntegrationTest extends TestCase
{
    /**
     * Test that the constants in FieldsInterface are defined correctly.
     */
    public function testFieldsInterfaceConstants(): void
    {
        $constants = [
            'ASSOCIATION' => 'association',
            'CHECKBOX' => 'checkbox',
            'COLOR' => 'color',
            'DATE' => 'date',
            'DATE_TIME' => 'date_time',
            'FILE' => 'file',
            'HIDDEN' => 'hidden',
            'HTML' => 'html',
            'RADIO' => 'radio',
            'SELECT' => 'select',
            'TEXT' => 'text',
            'TEXTAREA' => 'textarea',
            'TIME' => 'time',
        ];

        foreach ($constants as $constant => $expectedValue) {
            $this->assertSame(FieldsInterface::{$constant}, $expectedValue);
        }
    }

    /**
     * Test that the constants in TypeInterface are defined correctly.
     */
    public function testTypeInterfaceConstants(): void
    {
        $constants = [
            'COMMENT_META' => 'comment_meta',
            'POST_META' => 'post_meta',
            'TERM_META' => 'term_meta',
            'THEME_OPTIONS' => 'theme_options',
            'USER_META' => 'user_meta',
        ];

        foreach ($constants as $constant => $expectedValue) {
            $this->assertSame(TypeInterface::{$constant}, $expectedValue);
        }
    }
}
