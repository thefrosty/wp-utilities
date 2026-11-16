<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\WpAdmin\Models;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use TheFrosty\WpUtilities\Tests\Plugin\Framework\TestCase;
use TheFrosty\WpUtilities\WpAdmin\Models\OptionValueLabel;

/**
 * Class OptionValueLabelTest
 * @package TheFrosty\WpUtilities\Tests\WpAdmin\Models
 */
#[CoversClass(OptionValueLabel::class)]
#[Group('wp-admin')]
class OptionValueLabelTest extends TestCase
{
    public function testValueLabelAndArray(): void
    {
        $option = new OptionValueLabel('red', 'Red');

        $this->assertSame('red', $option->getValue());
        $this->assertSame('Red', $option->getLabel());
        $this->assertSame(
            [
                OptionValueLabel::KEY_VALUE => 'red',
                OptionValueLabel::KEY_LABEL => 'Red',
            ],
            $option->toArray()
        );
    }
}
