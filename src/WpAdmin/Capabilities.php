<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\WpAdmin;

use TheFrosty\WpUtilities\Plugin\HooksTrait;
use TheFrosty\WpUtilities\Plugin\WpHooksInterface;
use TheFrosty\WpUtilities\WpAdmin\Roles\RoleManager;
use WP_User;
use function array_flip;
use function array_key_exists;
use function get_current_user_id;
use function get_user_by;
use function in_array;
use function is_numeric;

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
        $this->addFilter('map_meta_cap', [$this, 'mapDoNotAllowCap'], 99, 4);
        $this->addFilter('editable_roles', [$this, 'removeSuperAdminRole']);
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
     * @param array $args Adds context to the cap, typically the object ID.
     * @return array
     */
    protected function mapDoNotAllowCap(array $caps, ?string $cap, int $user_id, array $args = []): array
    {
        if (self::userHasSuperAdminRole($user_id)) {
            return $caps;
        }

        // Users without the super admin role can't edit, promote or delete a super admin.
        if (
            in_array($cap, ['edit_user', 'promote_user', 'delete_user', 'remove_user'], true) &&
            isset($args[0]) && is_numeric($args[0]) &&
            self::userHasSuperAdminRole((int)$args[0])
        ) {
            $caps[] = self::DO_NOT_ALLOW;
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

    /**
     * Only users with the super admin role may assign it, otherwise any administrator could promote themselves
     * (or a new user) to `super_admin` and lift every restriction above.
     * @param array $roles The editable roles.
     * @return array
     */
    protected function removeSuperAdminRole(array $roles): array
    {
        if (!self::userHasSuperAdminRole(get_current_user_id())) {
            unset($roles[RoleManager::ROLE_SUPER_ADMIN]);
        }

        return $roles;
    }
}
