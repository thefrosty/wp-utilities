<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\WpAdmin\Users\Fields;

use TheFrosty\WpUtilities\WpAdmin\Users\Profile;
use TheFrosty\WpUtilities\Plugin\HooksTrait;
use TheFrosty\WpUtilities\Plugin\WpHooksInterface;
use function get_user_meta;

/**
 * Class UserMetaFieldRegistrar
 * @package TheFrosty\WpUtilities\WpAdmin\Users\Fields
 */
abstract class UserMetaFieldRegistrar implements WpHooksInterface
{

    use HooksTrait;

    /**
     * Add class hooks.
     */
    public function addHooks(): void
    {
        $this->addFilter(Profile::HOOK_NAME_REGISTER_USER_META_FIELDS, [$this, 'registerUserMetaFields']);
    }

    /**
     * Get the user meta value.
     * @param int $user_id
     * @param string $key
     * @return mixed
     */
    public static function getUserMeta(int $user_id, string $key): mixed
    {
        return get_user_meta($user_id, $key, true);
    }

    /**
     * Register new user meta fields.
     * @param \TheFrosty\WpUtilities\WpAdmin\Users\Models\UserMetaField[] $fields
     * @return \TheFrosty\WpUtilities\WpAdmin\Users\Models\UserMetaField[]
     */
    abstract protected function registerUserMetaFields(array $fields): array;
}
