<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\WpAdmin\Roles;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use TheFrosty\WpUtilities\Tests\Plugin\Framework\TestCase;
use TheFrosty\WpUtilities\WpAdmin\Roles\RoleManager;

/**
 * Class RoleManagerTest
 * @package TheFrosty\WpUtilities\Tests\WpAdmin\Roles
 */
#[CoversClass(RoleManager::class)]
#[Group('wp-admin')]
class RoleManagerTest extends TestCase
{
    public function testGetAllMappedCaps(): void
    {
        $caps = RoleManager::getAllMappedCaps('book', 'books');

        $this->assertCount(15, $caps);
        $this->assertSame([
            'edit_book',
            'read_book',
            'delete_book',
            'edit_books',
            'edit_others_books',
            'publish_books',
            'read_private_books',
            'read',
            'delete_books',
            'delete_private_books',
            'delete_published_books',
            'delete_others_books',
            'edit_private_books',
            'edit_published_books',
            'create_books',
        ], $caps);
    }
}
