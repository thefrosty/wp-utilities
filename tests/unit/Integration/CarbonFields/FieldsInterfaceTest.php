<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\Integration\CarbonFields;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\UsesClass;
use TheFrosty\WpUtilities\Integration\CarbonFields\FieldsInterface;
use TheFrosty\WpUtilities\Tests\Plugin\Framework\TestCase;

/**
 * Class FieldsInterfaceTest
 * @package TheFrosty\WpUtilities\Tests\Integration\CarbonFields
 */
#[CoversNothing]
#[UsesClass(FieldsInterface::class)]
#[Group('integration')]
class FieldsInterfaceTest extends TestCase
{
    /**
     * Test that all constants have correct values.
     */
    public function testConstantValues(): void
    {
        $this->assertSame('association', FieldsInterface::ASSOCIATION);
        $this->assertSame('checkbox', FieldsInterface::CHECKBOX);
        $this->assertSame('color', FieldsInterface::COLOR);
        $this->assertSame('date', FieldsInterface::DATE);
        $this->assertSame('date_time', FieldsInterface::DATE_TIME);
        $this->assertSame('file', FieldsInterface::FILE);
        $this->assertSame('hidden', FieldsInterface::HIDDEN);
        $this->assertSame('html', FieldsInterface::HTML);
        $this->assertSame('radio', FieldsInterface::RADIO);
        $this->assertSame('select', FieldsInterface::SELECT);
        $this->assertSame('text', FieldsInterface::TEXT);
        $this->assertSame('textarea', FieldsInterface::TEXTAREA);
        $this->assertSame('time', FieldsInterface::TIME);
    }
}
