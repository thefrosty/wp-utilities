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

/**
 * Class WpQueryTraitGetAllIdsTest
 * @package TheFrosty\WpUtilities\Tests\Api
 */
#[CoversTrait(WpQueryTrait::class)]
#[UsesTrait(Hash::class)]
#[UsesTrait(WpCacheTrait::class)]
#[UsesClass(Plugin::class)]
#[Group('api')]
class WpQueryTraitGetAllIdsTest extends TestCase
{
    private const string POST_TYPE = 'wpu_all_ids';

    private array $postIds = [];

    protected function setUp(): void
    {
        parent::setUp();
        register_post_type(self::POST_TYPE, ['public' => true]);
        for ($i = 1; $i <= 5; $i++) {
            $this->postIds[] = wp_insert_post([
                'post_title' => 'All ids ' . $i,
                'post_type' => self::POST_TYPE,
                'post_status' => 'publish',
            ]);
        }
    }

    protected function tearDown(): void
    {
        foreach ($this->postIds as $post_id) {
            wp_delete_post($post_id, true);
        }
        unregister_post_type(self::POST_TYPE);
        parent::tearDown();
    }

    public function testGetAllIdsReturnsEveryPostAcrossPagesExactlyOnce(): void
    {
        $query = new class {
            use WpQueryTrait;
        };

        // 5 posts at 2 per page = 3 pages. Starting at `paged` 0 returned page one twice and skipped page three.
        foreach (['wpQueryGetAllIds', 'wpQueryGetAllIdsCached'] as $method) {
            $ids = (new ReflectionMethod($query, $method))
                ->invoke($query, self::POST_TYPE, ['posts_per_page' => 2]);

            $this->assertCount(5, $ids, $method);
            $this->assertSameSize($ids, array_unique($ids), $method);
            $this->assertEqualsCanonicalizing($this->postIds, $ids, $method);
        }
    }

    public function testGetAllIdsReturnsAnEmptyListWhenNothingMatches(): void
    {
        $query = new class {
            use WpQueryTrait;
        };

        $ids = (new ReflectionMethod($query, 'wpQueryGetAllIds'))
            ->invoke($query, self::POST_TYPE, ['posts_per_page' => 2, 'post__in' => [PHP_INT_MAX]]);

        $this->assertSame([], $ids);
    }
}
