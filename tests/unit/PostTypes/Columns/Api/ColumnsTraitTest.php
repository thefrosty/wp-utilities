<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\PostTypes\Columns\Api;

use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\UsesTrait;
use ReflectionMethod;
use TheFrosty\WpUtilities\Plugin\HooksTrait;
use TheFrosty\WpUtilities\PostTypes\Columns\Api\ColumnsTrait;
use TheFrosty\WpUtilities\Tests\Plugin\Framework\TestCase;

/**
 * Class ColumnsTraitTest
 * @package TheFrosty\WpUtilities\Tests\PostTypes\Columns\Api
 */
#[CoversTrait(ColumnsTrait::class)]
// Inserting a post can run HooksTrait callbacks other tests registered on core hooks.
#[UsesTrait(HooksTrait::class)]
#[Group('post-types')]
class ColumnsTraitTest extends TestCase
{
    public function testRowActionsHtml(): void
    {
        wp_set_current_user(1);
        $post_id = wp_insert_post([
            'post_title' => 'Row "actions" <b>post</b>',
            'post_status' => 'publish',
        ]);
        $columns = new class {
            use ColumnsTrait;
        };

        ob_start();
        (new ReflectionMethod($columns, 'rowActionsHtml'))->invoke($columns, $post_id, 'post', 'related_post');
        $html = ob_get_clean();
        wp_delete_post($post_id, true);
        wp_set_current_user(0);

        $this->assertStringContainsString('data-select2-ajax="true"', $html);
        $this->assertStringContainsString('data-meta_key="related_post"', $html);
        $this->assertStringContainsString(sprintf('data-meta_value="%d"', $post_id), $html);
        $this->assertStringContainsString('>Edit</a>', $html);
        $this->assertStringContainsString('>Filter</span>', $html);
        $this->assertStringNotContainsString('<b>post</b>', $html);
    }
}
