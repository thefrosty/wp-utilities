<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\WpAdmin\Dashboard;

use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\UsesTrait;
use TheFrosty\WpUtilities\Api\Hash;
use TheFrosty\WpUtilities\Tests\Plugin\Framework\TestCase;
use TheFrosty\WpUtilities\WpAdmin\Dashboard\SimplePie;
use WP_Error;

/**
 * Class SimplePieTest
 * @package TheFrosty\WpUtilities\Tests\WpAdmin\Dashboard
 */
#[CoversTrait(SimplePie::class)]
#[UsesTrait(Hash::class)]
#[Group('wp-admin')]
class SimplePieTest extends TestCase
{
    protected function tearDown(): void
    {
        remove_all_filters('pre_http_request');
        parent::tearDown();
    }

    public function testGetFeedItemsReturnsAnEmptyListWhenTheFeedFails(): void
    {
        add_filter('pre_http_request', static fn(): WP_Error => new WP_Error('blocked', 'blocked'));
        $feed = new class {
            use SimplePie;
        };

        $this->assertSame([], $feed->getFeedItems(2, 'https://example.com/feed'));
    }
}
