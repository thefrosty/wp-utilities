<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\WpAdmin\Users;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\Attributes\UsesTrait;
use ReflectionMethod;
use ReflectionProperty;
use Symfony\Component\HttpFoundation\Request;
use TheFrosty\WpUtilities\Plugin\HttpFoundationRequestTrait;
use TheFrosty\WpUtilities\Tests\Plugin\Framework\TestCase;
use TheFrosty\WpUtilities\Utils\View;
use TheFrosty\WpUtilities\WpAdmin\Users\Fields\FieldType;
use TheFrosty\WpUtilities\WpAdmin\Users\Fields\Type;
use TheFrosty\WpUtilities\WpAdmin\Users\Fields\UserMetaFieldRegistrar;
use TheFrosty\WpUtilities\WpAdmin\Users\Models\UserMetaField;
use TheFrosty\WpUtilities\WpAdmin\Users\Profile;
use WP_User;

/**
 * Class ProfileTest
 * @package TheFrosty\WpUtilities\Tests\WpAdmin\Users
 */
#[CoversClass(Profile::class)]
#[UsesTrait(FieldType::class)]
#[UsesTrait(HttpFoundationRequestTrait::class)]
#[UsesClass(UserMetaField::class)]
#[UsesClass(UserMetaFieldRegistrar::class)]
#[UsesClass(View::class)]
#[Group('wp-admin')]
class ProfileTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        wp_set_current_user(1);
        $this->resetRequest();
    }

    protected function tearDown(): void
    {
        remove_all_filters(Profile::HOOK_NAME_REGISTER_USER_META_FIELDS);
        $this->resetRequest();
        parent::tearDown();
    }

    private function resetRequest(): void
    {
        $property = new ReflectionProperty(Profile::class, 'request');
        $property->setValue(null, null);
    }

    public function testAddHooksAndProfileMarkup(): void
    {
        $profile = new Profile();
        $profile->setRequest(Request::create('/'));
        $profile->addHooks();
        $this->assertNotFalse(has_action('show_user_profile'));
        $this->assertNotFalse(has_action('personal_options_update'));

        add_filter(Profile::HOOK_NAME_REGISTER_USER_META_FIELDS, static fn(): array => [
            new UserMetaField(['name' => 'city', 'label' => 'City']),
            'ignore-me',
        ]);
        $user = new WP_User(1);
        ob_start();
        (new ReflectionMethod(Profile::class, 'userProfile'))->invoke($profile, $user);
        $html = ob_get_clean();

        $this->assertStringContainsString('Extra Profile Fields', $html);
        $this->assertStringContainsString('city', $html);
    }

    public function testSaveProfileFieldsUpdatesUserMeta(): void
    {
        $profile = new Profile();
        $nonce = wp_create_nonce(sprintf(Profile::NONCE_ACTION_S, 1));
        $profile->setRequest(Request::create('/', 'POST', [
            Profile::ACTION => $nonce,
            'city' => 'Austin',
        ]));
        add_filter(Profile::HOOK_NAME_REGISTER_USER_META_FIELDS, static fn(): array => [
            new UserMetaField(['name' => 'city', 'label' => 'City']),
        ]);

        (new ReflectionMethod(Profile::class, 'saveProfileFields'))->invoke($profile, 1);
        $this->assertSame('Austin', get_user_meta(1, 'city', true));
    }

    public function testSaveProfileFieldsIgnoresAnInvalidNonce(): void
    {
        $profile = new Profile();
        $profile->setRequest(Request::create('/', 'POST', [
            Profile::ACTION => 'bad',
            'city' => 'Nope',
        ]));
        add_filter(Profile::HOOK_NAME_REGISTER_USER_META_FIELDS, static fn(): array => [
            new UserMetaField(['name' => 'city', 'label' => 'City']),
        ]);

        (new ReflectionMethod(Profile::class, 'saveProfileFields'))->invoke($profile, 1);
        $this->assertNotSame('Nope', get_user_meta(1, 'city', true));
    }

    public function testSaveProfileFieldsSkipsFieldsHiddenByTheirCondition(): void
    {
        delete_user_meta(1, 'is_vip');
        $profile = new Profile();
        $profile->setRequest(Request::create('/', 'POST', [
            Profile::ACTION => wp_create_nonce(sprintf(Profile::NONCE_ACTION_S, 1)),
            'is_vip' => '1',
            'city' => 'Austin',
        ]));
        add_filter(Profile::HOOK_NAME_REGISTER_USER_META_FIELDS, static fn(): array => [
            new UserMetaField([
                'name' => 'is_vip',
                'label' => 'VIP',
                'type' => Type::CHECKBOX,
                'condition' => static fn(): bool => true, // Hidden from this user.
            ]),
            new UserMetaField(['name' => 'city', 'label' => 'City']),
        ]);

        (new ReflectionMethod(Profile::class, 'saveProfileFields'))->invoke($profile, 1);

        $this->assertSame('', get_user_meta(1, 'is_vip', true));
        $this->assertSame('Austin', get_user_meta(1, 'city', true));
    }

    public function testSaveProfileFieldsSanitizesByTypeAndSkipsArrays(): void
    {
        $profile = new Profile();
        $profile->setRequest(Request::create('/', 'POST', [
            Profile::ACTION => wp_create_nonce(sprintf(Profile::NONCE_ACTION_S, 1)),
            'city' => '<script>x</script>Austin',
            'newsletter' => 'yes-please',
            'tags' => ['a', 'b'],
        ]));
        add_filter(Profile::HOOK_NAME_REGISTER_USER_META_FIELDS, static fn(): array => [
            new UserMetaField(['name' => 'city', 'label' => 'City']),
            new UserMetaField(['name' => 'newsletter', 'label' => 'Newsletter', 'type' => Type::CHECKBOX]),
            new UserMetaField(['name' => 'tags', 'label' => 'Tags']),
        ]);

        (new ReflectionMethod(Profile::class, 'saveProfileFields'))->invoke($profile, 1);

        $this->assertSame('Austin', get_user_meta(1, 'city', true));
        $this->assertSame('off', get_user_meta(1, 'newsletter', true));
        $this->assertSame('', get_user_meta(1, 'tags', true));
    }

    public function testIsFieldHidden(): void
    {
        $this->assertFalse(Profile::isFieldHidden(new UserMetaField(['name' => 'a', 'label' => 'A'])));
        $this->assertTrue(Profile::isFieldHidden(new UserMetaField([
            'name' => 'b',
            'label' => 'B',
            'condition' => static fn(): bool => true,
        ])));
    }
}
