<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\Api;

use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\Attributes\UsesTrait;
use ReflectionMethod;
use TheFrosty\WpUtilities\Api\Hash;
use TheFrosty\WpUtilities\Api\WpCacheTrait;
use TheFrosty\WpUtilities\Api\WpQueryTrait;
use TheFrosty\WpUtilities\Plugin\Plugin;
use TheFrosty\WpUtilities\Tests\Plugin\Framework\TestCase;
use WP_Query;

/**
 * Class WpQueryTraitTest
 * @package TheFrosty\WpUtilities\Tests\Api
 */
#[CoversTrait(WpQueryTrait::class)]
#[UsesTrait(Hash::class)]
#[UsesTrait(WpCacheTrait::class)]
#[UsesClass(Plugin::class)]
#[Group('api')]
class WpQueryTraitTest extends TestCase
{
    private const string POST_TYPE = 'wpu_query_trait';

    private object $query;

    private array $postIds = [];

    protected function setUp(): void
    {
        parent::setUp();
        register_post_type(self::POST_TYPE, ['public' => true]);
        foreach (['publish', 'draft', 'private'] as $status) {
            $this->postIds[$status] = wp_insert_post([
                'post_title' => 'Query trait ' . $status,
                'post_type' => self::POST_TYPE,
                'post_status' => $status,
            ]);
        }
        $this->query = new class {
            use WpQueryTrait;
        };
    }

    protected function tearDown(): void
    {
        foreach ($this->postIds as $post_id) {
            wp_delete_post($post_id, true);
        }
        unregister_post_type(self::POST_TYPE);
        remove_all_filters(Plugin::TAG . '/wp_query_defaults');
        remove_all_filters('deprecated_argument_trigger_error');
        parent::tearDown();
    }

    public function testWpQueryAppliesTheFilterableDefaults(): void
    {
        $query = $this->invoke('wpQuery', self::POST_TYPE);

        $this->assertInstanceOf(WP_Query::class, $query);
        $this->assertSame(self::POST_TYPE, $query->get('post_type'));
        $this->assertEqualsCanonicalizing(['publish', 'future', 'draft'], $query->get('post_status'));
        // Private posts aren't part of the default statuses.
        $this->assertEqualsCanonicalizing(
            [$this->postIds['publish'], $this->postIds['draft']],
            wp_list_pluck($query->posts, 'ID')
        );

        add_filter(
            Plugin::TAG . '/wp_query_defaults',
            static fn(array $defaults): array => ['post_status' => ['draft']] + $defaults
        );
        $drafts = $this->invoke('wpQuery', self::POST_TYPE, ['fields' => 'ids']);
        $this->assertSame([$this->postIds['draft']], $drafts->posts);
    }

    public function testWpQueryCachedReturnsTheCachedQuery(): void
    {
        $first = $this->invoke('wpQueryCached', self::POST_TYPE, ['fields' => 'ids'], 60);
        $key = $this->query->getQueryCacheKey();
        $this->assertNotEmpty($first->posts);
        $this->assertInstanceOf(WP_Query::class, wp_cache_get($key, $this->query->getCacheGroup()));

        // Served from the cache (the object cache stores a copy).
        $second = $this->invoke('wpQueryCached', self::POST_TYPE, ['fields' => 'ids']);
        $this->assertSame($key, $this->query->getQueryCacheKey());
        $this->assertSame($first->posts, $second->posts);

        // Queries without results aren't cached.
        $this->invoke('wpQueryCached', self::POST_TYPE, ['post__in' => [PHP_INT_MAX]]);
        $this->assertFalse(wp_cache_get($this->query->getQueryCacheKey(), $this->query->getCacheGroup()));
    }

    public function testGetAllIds(): void
    {
        $expected = [$this->postIds['publish'], $this->postIds['draft']];

        $this->assertEqualsCanonicalizing($expected, $this->invoke('wpQueryGetAllIds', self::POST_TYPE));
        $this->assertEqualsCanonicalizing($expected, $this->invoke('wpQueryGetAllIdsCached', self::POST_TYPE));
    }

    public function testGetAllIdsWithAnExpirationIsDeprecated(): void
    {
        $deprecated = [];
        add_action('deprecated_argument_run', static function (string $function) use (&$deprecated): void {
            $deprecated[] = $function;
        });
        add_filter('deprecated_argument_trigger_error', '__return_false');

        $ids = $this->invoke('wpQueryGetAllIds', self::POST_TYPE, [], 60);
        remove_all_actions('deprecated_argument_run');

        $this->assertCount(2, $ids);
        $this->assertSame(['wpQueryGetAllIds'], $deprecated);
    }

    private function invoke(string $method, mixed ...$args): mixed
    {
        return (new ReflectionMethod($this->query, $method))->invoke($this->query, ...$args);
    }
}
