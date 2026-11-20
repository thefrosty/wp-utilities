<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\Api;

use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\UsesTrait;
use TheFrosty\WpUtilities\Api\Hash;
use TheFrosty\WpUtilities\Api\WpCacheTrait;
use TheFrosty\WpUtilities\Tests\Plugin\Framework\TestCase;

/**
 * Class WpCacheTraitTest
 * @package TheFrosty\WpUtilities\Tests\Api
 */
#[CoversTrait(WpCacheTrait::class)]
#[UsesTrait(Hash::class)]
#[Group('api')]
class WpCacheTraitTest extends TestCase
{
    public function testQueryCacheKeyAndGroup(): void
    {
        $cache = $this->cache();

        $this->assertNull($cache->getQueryCacheKey());
        $this->assertSame('key-1', $cache->setQueryCacheKey('key-1'));
        $this->assertSame('key-1', $cache->getQueryCacheKey());

        $this->assertSame($cache::class, $cache->getCacheGroup());
        $cache->setCacheGroup('wpu-group');
        $this->assertSame('wpu-group', $cache->getCacheGroup());
    }

    public function testSetGetAndDeleteCache(): void
    {
        $cache = $this->cache();
        $cache->setCacheGroup('wpu-cache-test');

        $this->assertTrue($cache->setCache('value', ['a' => 1]));
        $this->assertSame(['a' => 1], $cache->getCache('value', null, false, $found));
        $this->assertTrue($found);
        $this->assertSame(['a' => 1], wp_cache_get('value', 'wpu-cache-test'));

        $this->assertTrue($cache->setCache('other', 'group', 'wpu-other-group', 60));
        $this->assertSame('group', $cache->getCache('other', 'wpu-other-group'));
        $this->assertFalse($cache->getCache('other'));

        $this->assertTrue($cache->deleteCache('value'));
        $this->assertFalse($cache->getCache('value', found: $found));
        $this->assertFalse($found);
        $this->assertTrue($cache->deleteCache('other', 'wpu-other-group'));
    }

    private function cache(): object
    {
        return new class {
            use WpCacheTrait;
        };
    }
}
