<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\RestApi;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\UsesClass;
use ReflectionMethod;
use TheFrosty\WpUtilities\Plugin\Plugin;
use TheFrosty\WpUtilities\RestApi\PostTypeFilter;
use TheFrosty\WpUtilities\Tests\Plugin\Framework\TestCase;
use WP_REST_Request;

/**
 * Class PostTypeFilterTest
 * @package TheFrosty\WpUtilities\Tests\RestApi
 */
#[CoversClass(PostTypeFilter::class)]
#[UsesClass(Plugin::class)]
#[Group('rest-api')]
class PostTypeFilterTest extends TestCase
{
    protected function tearDown(): void
    {
        remove_all_actions('rest_api_init');
        remove_all_filters(PostTypeFilter::HOOK_ALLOW_META_QUERY);
        wp_set_current_user(0);
        parent::tearDown();
    }

    public function testAddFilterParamMergesAllowedQueryVars(): void
    {
        wp_set_current_user(1);
        $filter = new PostTypeFilter();
        $filter->addHooks();
        do_action('rest_api_init', rest_get_server());

        $request = new WP_REST_Request('GET', '/wp/v2/posts');
        $request->set_param(PostTypeFilter::QUERY_PARAM, [
            'posts_per_page' => 10,
            'meta_key' => 'color',
            'posts_per_page_ignored' => 500,
        ]);
        $request->set_param('posts_per_page', 500);

        $method = new ReflectionMethod(PostTypeFilter::class, 'addFilterParam');
        $args = $method->invoke($filter, ['post_type' => 'post'], $request);

        $this->assertSame(10, $args['posts_per_page']);
        $this->assertSame('color', $args['meta_key']);
    }

    public function testAddFilterParamDropsMetaQueryVarsForAnonymousUsers(): void
    {
        wp_set_current_user(0);
        $filter = new PostTypeFilter();
        $request = new WP_REST_Request('GET', '/wp/v2/posts');
        $request->set_param(PostTypeFilter::QUERY_PARAM, [
            'meta_key' => '_secret',
            'meta_value' => '^a',
            'meta_compare' => 'REGEXP',
            'meta_query' => [['key' => '_secret', 'value' => 'a', 'compare' => 'LIKE']],
            'post__in' => [1, 2],
        ]);

        $method = new ReflectionMethod(PostTypeFilter::class, 'addFilterParam');
        $args = $method->invoke($filter, ['post_type' => 'post'], $request);

        $this->assertArrayNotHasKey('meta_key', $args);
        $this->assertArrayNotHasKey('meta_value', $args);
        $this->assertArrayNotHasKey('meta_compare', $args);
        $this->assertArrayNotHasKey('meta_query', $args);
        $this->assertSame([1, 2], $args['post__in']);
    }

    public function testMetaQueryVarsCanBeAllowedViaFilter(): void
    {
        wp_set_current_user(0);
        add_filter(PostTypeFilter::HOOK_ALLOW_META_QUERY, '__return_true');
        $filter = new PostTypeFilter();
        $request = new WP_REST_Request('GET', '/wp/v2/posts');
        $request->set_param(PostTypeFilter::QUERY_PARAM, ['meta_key' => 'color']);

        $method = new ReflectionMethod(PostTypeFilter::class, 'addFilterParam');
        $args = $method->invoke($filter, ['post_type' => 'post'], $request);

        $this->assertSame('color', $args['meta_key']);
    }

    public function testAddFilterParamIgnoresAnEmptyFilter(): void
    {
        $filter = new PostTypeFilter();
        $method = new ReflectionMethod(PostTypeFilter::class, 'addFilterParam');
        $request = new WP_REST_Request('GET', '/wp/v2/posts');

        $this->assertSame(['post_type' => 'post'], $method->invoke($filter, ['post_type' => 'post'], $request));
    }
}
