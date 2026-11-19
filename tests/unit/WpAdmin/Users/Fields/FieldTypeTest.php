<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\WpAdmin\Users\Fields;

use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\UsesClass;
use TheFrosty\WpUtilities\Models\BaseModel;
use TheFrosty\WpUtilities\Tests\Plugin\Framework\TestCase;
use TheFrosty\WpUtilities\WpAdmin\Users\Fields\FieldType;
use TheFrosty\WpUtilities\WpAdmin\Users\Fields\Type;
use TheFrosty\WpUtilities\WpAdmin\Users\Fields\UserMetaFieldRegistrar;
use TheFrosty\WpUtilities\WpAdmin\Users\Models\UserMetaField;
use WP_User;

/**
 * Class FieldTypeTest
 * @package TheFrosty\WpUtilities\Tests\WpAdmin\Users\Fields
 */
#[CoversTrait(FieldType::class)]
#[UsesClass(BaseModel::class)]
#[UsesClass(UserMetaField::class)]
#[UsesClass(UserMetaFieldRegistrar::class)]
#[Group('wp-admin')]
class FieldTypeTest extends TestCase
{
    private object $fieldType;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fieldType = new class {
            use FieldType;
        };
    }

    protected function tearDown(): void
    {
        delete_user_meta(1, 'wpu_checkbox');
        delete_user_meta(1, 'wpu_text');
        parent::tearDown();
    }

    public function testCheckboxRendersCheckedStateAndDescription(): void
    {
        $field = new UserMetaField([
            'name' => 'wpu_checkbox',
            'label' => 'Checkbox',
            'type' => Type::CHECKBOX,
            'description' => 'Tick <strong>me</strong><script>x</script>',
        ]);
        $user = new WP_User(1);

        $html = $this->fieldType->checkbox($field, $user);
        $this->assertStringContainsString('<input type="hidden" name="wpu_checkbox" value="off">', $html);
        $this->assertStringContainsString('type="checkbox" name="wpu_checkbox" id="wp_utilities_wpu_checkbox"', $html);
        $this->assertStringNotContainsString('checked', $html);
        $this->assertStringContainsString('<span class="description">Tick <strong>me</strong>', $html);
        $this->assertStringNotContainsString('<script>', $html);

        update_user_meta(1, 'wpu_checkbox', '1');
        $this->assertStringContainsString("checked='checked'", $this->fieldType->checkbox($field, $user));
    }

    public function testTextRendersTheEscapedValueWithoutADescription(): void
    {
        update_user_meta(1, 'wpu_text', '"><b>Austin');
        $field = new UserMetaField(['name' => 'wpu_text', 'label' => 'Text']);

        $html = $this->fieldType->text($field, new WP_User(1));

        $this->assertStringContainsString('type="text" name="wpu_text" id="wp_utilities_wpu_text"', $html);
        $this->assertStringContainsString('value="&quot;&gt;&lt;b&gt;Austin"', $html);
        $this->assertStringNotContainsString('class="description"', $html);
    }
}
