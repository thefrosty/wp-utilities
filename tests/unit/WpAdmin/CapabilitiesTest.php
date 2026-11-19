<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\WpAdmin;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\UsesClass;
use TheFrosty\WpUtilities\Tests\Plugin\Framework\TestCase;
use TheFrosty\WpUtilities\WpAdmin\Capabilities;
use TheFrosty\WpUtilities\WpAdmin\Roles\RoleManager;

/**
 * Class CapabilitiesTest
 * @package TheFrosty\WpUtilities\Tests\WpAdmin
 */
#[CoversClass(Capabilities::class)]
#[UsesClass(RoleManager::class)]
#[Group('wp-admin')]
class CapabilitiesTest extends TestCase
{
    private int $adminId;

    private int $superAdminId;

    protected function setUp(): void
    {
        parent::setUp();
        add_role(RoleManager::ROLE_SUPER_ADMIN, 'Super Admin', get_role('administrator')->capabilities);
        $this->adminId = wp_insert_user([
            'user_login' => 'caps_admin_' . wp_generate_password(6, false),
            'user_pass' => wp_generate_password(),
            'role' => RoleManager::WP_ROLE_ADMINISTRATOR,
        ]);
        $this->superAdminId = wp_insert_user([
            'user_login' => 'caps_super_' . wp_generate_password(6, false),
            'user_pass' => wp_generate_password(),
            'role' => RoleManager::ROLE_SUPER_ADMIN,
        ]);
        (new Capabilities())->addHooks();
    }

    protected function tearDown(): void
    {
        remove_all_filters('map_meta_cap');
        remove_all_filters('editable_roles');
        wp_set_current_user(0);
        require_once ABSPATH . 'wp-admin/includes/user.php';
        wp_delete_user($this->adminId);
        wp_delete_user($this->superAdminId);
        remove_role(RoleManager::ROLE_SUPER_ADMIN);
        parent::tearDown();
    }

    public function testAdministratorsCannotAssignTheSuperAdminRole(): void
    {
        wp_set_current_user($this->adminId);
        $roles = apply_filters('editable_roles', wp_roles()->roles);
        $this->assertArrayNotHasKey(RoleManager::ROLE_SUPER_ADMIN, $roles);
        $this->assertArrayHasKey(RoleManager::WP_ROLE_ADMINISTRATOR, $roles);

        wp_set_current_user($this->superAdminId);
        $roles = apply_filters('editable_roles', wp_roles()->roles);
        $this->assertArrayHasKey(RoleManager::ROLE_SUPER_ADMIN, $roles);
    }

    public function testAdministratorsCannotEditPromoteOrDeleteASuperAdmin(): void
    {
        foreach (['edit_user', 'promote_user', 'delete_user'] as $cap) {
            $this->assertFalse(user_can($this->adminId, $cap, $this->superAdminId), $cap);
        }
        $this->assertTrue(user_can($this->superAdminId, 'edit_user', $this->adminId));
    }

    public function testRestrictedCapsAreDeniedToNonSuperAdmins(): void
    {
        $this->assertFalse(user_can($this->adminId, 'install_plugins'));
        $this->assertFalse(user_can($this->adminId, 'edit_files'));
    }
}
