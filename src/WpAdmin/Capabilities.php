<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\WpAdmin;

use TheFrosty\WpUtilities\WpAdmin\Roles\RoleManager;
use TheFrosty\WpUtilities\Plugin\HooksTrait;
use TheFrosty\WpUtilities\Plugin\WpHooksInterface;
use WP_User;
use function array_flip;
use function array_key_exists;
use function get_user_by;

/**
 * Class Capabilities.
 * @package TheFrosty\WpUtilities\WpAdmin
 */
class Capabilities implements WpHooksInterface
{

    use HooksTrait;

    public const string DO_NOT_ALLOW = 'do_not_allow';

    /**
     * Add class hooks.
     */
    public function addHooks(): void
    {
        $this->addFilter('map_meta_cap', [$this, 'mapDoNotAllowCap'], 99, 3);
    }

    /**
     * Does the user have a "role".
     * @param int $user_id User ID.
     * @param string $role User ID.
     * @return bool
     */
    public static function userHasRole(int $user_id, string $role): bool
    {
        $user = get_user_by('ID', $user_id);
        $roles = $user instanceof WP_User ? $user->roles : [];

        return array_key_exists($role, array_flip($roles));
    }

    /**
     * Does the user have the correct "role".
     * Note we've arbitrarily created a "Super Admin" role from the Users > Roles page.
     * @param int $user_id User ID.
     * @return bool
     */
    public static function userHasSuperAdminRole(int $user_id): bool
    {
        return self::userHasRole($user_id, RoleManager::ROLE_SUPER_ADMIN);
    }

    /**
     * Filters a user's capabilities. In this case we're disabling all users caps that match those in the switch
     * case below.
     * @param array $caps Returns the user's actual capabilities.
     * @param string|null $cap Capability name.
     * @param int $user_id The user ID.
     * @return array
     */
    protected function mapDoNotAllowCap(array $caps, ?string $cap, int $user_id): array
    {
        if (self::userHasSuperAdminRole($user_id)) {
            return $caps;
        }

        switch ($cap) {
            case 'edit_files':
            case 'edit_plugins':
            case 'edit_themes':
            case 'update_plugins':
            case 'delete_plugins':
            case 'install_plugins':
            case 'upload_plugins':
            case 'update_themes':
            case 'delete_themes':
            case 'install_themes':
            case 'upload_themes':
            case 'update_core':
            case 'delete_users':
                $caps[] = self::DO_NOT_ALLOW;
        }

        return $caps;
    }
}
