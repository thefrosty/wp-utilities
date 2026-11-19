<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\WpAdmin\Users\Models;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\UsesClass;
use TheFrosty\WpUtilities\Models\BaseModel;
use TheFrosty\WpUtilities\Tests\Plugin\Framework\TestCase;
use TheFrosty\WpUtilities\WpAdmin\Users\Fields\Type;
use TheFrosty\WpUtilities\WpAdmin\Users\Models\UserMetaField;

/**
 * Class UserMetaFieldTest
 * @package TheFrosty\WpUtilities\Tests\WpAdmin\Users\Models
 */
#[CoversClass(UserMetaField::class)]
#[UsesClass(BaseModel::class)]
#[Group('wp-admin')]
class UserMetaFieldTest extends TestCase
{
    public function testPopulatesAndReturnsEveryField(): void
    {
        $condition = static fn(): bool => true;
        $field = new UserMetaField([
            UserMetaField::FIELD_CONDITION => $condition,
            UserMetaField::FIELD_DESCRIPTION => 'Shown under the field',
            UserMetaField::FIELD_LABEL => 'Newsletter',
            UserMetaField::FIELD_NAME => 'newsletter',
            UserMetaField::FIELD_TYPE => Type::CHECKBOX,
        ]);

        $this->assertSame($condition, $field->getCondition());
        $this->assertSame('Shown under the field', $field->getDescription());
        $this->assertSame('Newsletter', $field->getLabel());
        $this->assertSame('newsletter', $field->getName());
        $this->assertSame(Type::CHECKBOX, $field->getType());
    }

    public function testDefaultsAndSerialization(): void
    {
        $field = new UserMetaField([
            UserMetaField::FIELD_LABEL => 'City',
            UserMetaField::FIELD_NAME => 'city',
        ]);

        $this->assertNull($field->getCondition());
        $this->assertNull($field->getDescription());
        $this->assertSame(Type::TEXT, $field->getType());
        $this->assertSame([
            UserMetaField::FIELD_DESCRIPTION => null,
            UserMetaField::FIELD_LABEL => 'City',
            UserMetaField::FIELD_NAME => 'city',
            UserMetaField::FIELD_TYPE => Type::TEXT,
        ], $field->toArray());
    }
}
