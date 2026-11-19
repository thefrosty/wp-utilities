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

    public function testGetFeedItemsReturnsTheFeedItems(): void
    {
        $this->fakeFeed('<item><title>One</title><link>https://example.com/1</link></item>'
            . '<item><title>Two</title><link>https://example.com/2</link></item>');
        $feed = new class {
            use SimplePie;
        };

        $items = $feed->getFeedItems(1, 'https://example.com/feed-items-' . wp_generate_password(6, false));

        $this->assertCount(1, $items);
        $this->assertSame('One', $items[0]->get_title());
    }

    public function testGetFeedItemsReturnsAnEmptyListForAFeedWithoutItems(): void
    {
        $this->fakeFeed('');
        $feed = new class {
            use SimplePie;
        };

        $this->assertSame([], $feed->getFeedItems(1, 'https://example.com/feed-empty-' . wp_generate_password(6, false)));
    }

    private function fakeFeed(string $items): void
    {
        $rss = '<?xml version="1.0" encoding="UTF-8"?><rss version="2.0"><channel><title>Feed</title>'
            . '<link>https://example.com/</link><description>d</description>' . $items . '</channel></rss>';
        add_filter('pre_http_request', static function () use ($rss): array {
            return [
                'headers' => ['content-type' => 'application/rss+xml'],
                'body' => $rss,
                'response' => ['code' => 200, 'message' => 'OK'],
                'cookies' => [],
                'filename' => null,
            ];
        });
    }
}
