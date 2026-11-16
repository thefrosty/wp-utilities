<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\WpAdmin;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\UsesClass;
use TheFrosty\WpUtilities\Plugin\Plugin;
use TheFrosty\WpUtilities\Tests\Plugin\Framework\TestCase;
use TheFrosty\WpUtilities\WpAdmin\AdminColumns;

/**
 * Class AdminColumnsTest
 * @package TheFrosty\WpUtilities\Tests\WpAdmin
 */
#[CoversClass(AdminColumns::class)]
#[UsesClass(Plugin::class)]
#[Group('wp-admin')]
class AdminColumnsTest extends TestCase
{
    public function testColumnsMoveDateToTheEndAndForwardCustomContent(): void
    {
        add_filter(AdminColumns::TAG_MANAGE_POST_TYPES, static fn(): array => ['book']);
        add_filter(AdminColumns::TAG_MANAGE_POSTS_COLUMNS, static function (array $columns): array {
            $columns['isbn'] = 'ISBN';

            return $columns;
        });
        $seen = null;
        add_action(
            AdminColumns::TAG_MANAGE_POSTS_CUSTOM_COLUMN,
            static function (string $column, int $postId, string $postType) use (&$seen): void {
                $seen = [$column, $postId, $postType];
            },
            10,
            3
        );
        add_filter(AdminColumns::TAG_MANAGE_MANAGE_EDIT_SORTABLE_COLUMNS, static function (array $columns): array {
            $columns['isbn'] = 'isbn';

            return $columns;
        });

        $columns = new AdminColumns();
        $columns->addHooks();

        $result = apply_filters('manage_book_posts_columns', ['title' => 'Title', 'date' => 'Date']);
        $this->assertSame(['title' => 'Title', 'isbn' => 'ISBN', 'date' => 'Date'], $result);

        ob_start();
        do_action('manage_book_posts_custom_column', 'isbn', 12);
        ob_end_clean();
        $this->assertSame(['isbn', 12, 'book'], $seen);

        $sortable = apply_filters('manage_edit-book_sortable_columns', ['title' => 'title']);
        $this->assertArrayHasKey('isbn', $sortable);
    }
}
