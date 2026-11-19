<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\WpAdmin\Users\Fields;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\Attributes\UsesTrait;
use TheFrosty\WpUtilities\Models\BaseModel;
use TheFrosty\WpUtilities\Plugin\HooksTrait;
use TheFrosty\WpUtilities\Tests\Plugin\Framework\TestCase;
use TheFrosty\WpUtilities\WpAdmin\Users\Fields\UserMetaFieldRegistrar;
use TheFrosty\WpUtilities\WpAdmin\Users\Models\UserMetaField;
use TheFrosty\WpUtilities\WpAdmin\Users\Profile;

/**
 * Class UserMetaFieldRegistrarTest
 * @package TheFrosty\WpUtilities\Tests\WpAdmin\Users\Fields
 */
#[CoversClass(UserMetaFieldRegistrar::class)]
#[UsesClass(BaseModel::class)]
#[UsesClass(UserMetaField::class)]
#[UsesTrait(HooksTrait::class)]
#[Group('wp-admin')]
class UserMetaFieldRegistrarTest extends TestCase
{
    protected function tearDown(): void
    {
        remove_all_filters(Profile::HOOK_NAME_REGISTER_USER_META_FIELDS);
        delete_user_meta(1, 'wpu_registrar');
        parent::tearDown();
    }

    public function testRegistersFieldsOnTheProfileHook(): void
    {
        $registrar = new class extends UserMetaFieldRegistrar {
            protected function registerUserMetaFields(array $fields): array
            {
                $fields[] = new UserMetaField(['name' => 'wpu_registrar', 'label' => 'Registrar']);

                return $fields;
            }
        };
        $registrar->addHooks();

        $fields = apply_filters(Profile::HOOK_NAME_REGISTER_USER_META_FIELDS, []);
        $this->assertCount(1, $fields);
        $this->assertSame('wpu_registrar', $fields[0]->getName());
    }

    public function testGetUserMeta(): void
    {
        update_user_meta(1, 'wpu_registrar', 'value');

        $this->assertSame('value', UserMetaFieldRegistrar::getUserMeta(1, 'wpu_registrar'));
        $this->assertSame('', UserMetaFieldRegistrar::getUserMeta(1, 'wpu_missing_key'));
    }
}
