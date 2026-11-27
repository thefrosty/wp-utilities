<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\WpAdmin;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\Attributes\UsesTrait;
use TheFrosty\WpUtilities\Api\Hash;
use TheFrosty\WpUtilities\Api\TransientsTrait;
use TheFrosty\WpUtilities\Api\WpCacheTrait;
use TheFrosty\WpUtilities\Api\WpRemote;
use TheFrosty\WpUtilities\Plugin\Plugin;
use TheFrosty\WpUtilities\Tests\Plugin\Framework\TestCase;
use TheFrosty\WpUtilities\Utils\View;
use TheFrosty\WpUtilities\WpAdmin\Dashboard\SimplePie;
use TheFrosty\WpUtilities\WpAdmin\Dashboard\Widget;
use TheFrosty\WpUtilities\WpAdmin\DashboardWidget;
use WP_Error;
use WP_Http;

/**
 * Class DashboardWidgetTest
 * @package TheFrosty\WpUtilities\Tests\WpAdmin
 */
#[CoversClass(DashboardWidget::class)]
#[UsesClass(Plugin::class)]
#[UsesClass(View::class)]
#[UsesClass(Widget::class)]
#[UsesTrait(Hash::class)]
#[UsesTrait(SimplePie::class)]
#[UsesTrait(TransientsTrait::class)]
#[UsesTrait(WpCacheTrait::class)]
#[UsesTrait(WpRemote::class)]
#[Group('wp-admin')]
class DashboardWidgetTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        require_once ABSPATH . 'wp-admin/includes/dashboard.php';
        set_current_screen('dashboard');
    }

    protected function tearDown(): void
    {
        remove_all_actions('load-index.php');
        remove_all_actions('wp_dashboard_setup');
        remove_all_filters('pre_http_request');
        parent::tearDown();
    }

    public function testAddsTheDashboardWidgetWhenAllowed(): void
    {
        add_filter('pre_http_request', static fn(): WP_Error => new WP_Error('blocked', 'blocked'));
        $widget = new DashboardWidget([
            Widget::FEED_URL => 'https://example.com/feed',
            Widget::WIDGET_ID => 'example_widget',
            Widget::WIDGET_NAME => 'Example',
        ]);
        $widget->addHooks();
        do_action('load-index.php');
        do_action('wp_dashboard_setup');

        $registered = $GLOBALS['wp_meta_boxes']['dashboard']['normal']['core']['example_widget'] ?? null;
        $this->assertIsArray($registered);
        $this->assertSame('example_widget', $widget->getWidget()->getWidgetId());

        ob_start();
        $registered['callback']();
        $html = ob_get_clean();
        $this->assertStringContainsString('rss-widget', $html);
    }

    public function testRendersAnRssTypeWidget(): void
    {
        $rss = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0"><channel><title>Feed</title><link>https://example.com/</link><description>d</description>
<item><title>First &lt;b&gt;post&lt;/b&gt;</title><link>https://example.com/first</link>
<pubDate>Thu, 19 Nov 2026 10:00:00 +0000</pubDate>
<description>&lt;script&gt;alert(1)&lt;/script&gt;Summary text</description></item>
</channel></rss>
XML;
        add_filter('pre_http_request', static fn(): array => [
            'headers' => ['content-type' => 'application/rss+xml'],
            'body' => $rss,
            'response' => ['code' => 200, 'message' => 'OK'],
            'cookies' => [],
            'filename' => null,
        ]);
        $widget = new DashboardWidget([
            Widget::FEED_URL => 'https://example.com/rss-feed-' . wp_generate_password(6, false),
            Widget::TYPE => Widget::TYPE_RSS,
            Widget::WIDGET_ID => 'rss_widget',
            Widget::WIDGET_NAME => 'RSS',
        ]);
        $widget->addHooks();
        do_action('load-index.php');
        do_action('wp_dashboard_setup');

        $registered = $GLOBALS['wp_meta_boxes']['dashboard']['normal']['core']['rss_widget'] ?? null;
        $this->assertIsArray($registered);
        ob_start();
        $registered['callback']();
        $html = ob_get_clean();

        $this->assertStringContainsString('class="rsswidget"', $html);
        $this->assertStringContainsString('utm_campaign=rss_widget', $html);
        $this->assertStringContainsString('Summary text', $html);
        $this->assertStringNotContainsString('<script', $html);
        $this->assertStringNotContainsString('<b>', $html);
    }

    public function testRestWidgetRendersAnErrorForANon2xxResponse(): void
    {
        // A REST API 404, e.g. a wrong feed URL. Previously this was decoded, cached for a week and iterated,
        // raising "Attempt to read property "link" on string".
        add_filter('pre_http_request', static fn(): array => [
            'headers' => ['content-type' => 'application/json'],
            'body' => '{"code":"rest_no_route","message":"No route was found.","data":{"status":404}}',
            'response' => ['code' => WP_Http::NOT_FOUND, 'message' => 'Not Found'],
            'cookies' => [],
            'filename' => null,
        ]);
        $widget = $this->restWidget('https://example.com/wp-json/wp/v2/missing-' . wp_generate_password(6, false));

        $html = $this->renderRest($widget);

        $this->assertStringContainsString('Error fetching feed', $html);
        $this->assertStringNotContainsString('rsswidget', $html);
    }

    public function testRestWidgetSkipsItemsThatAreNotPosts(): void
    {
        $widget = $this->restWidget('https://example.com/wp-json/wp/v2/posts');
        $post = (object)[
            'link' => 'https://example.com/hello/',
            'title' => (object)['rendered' => 'Hello <b>world</b>'],
            'date' => '2026-11-27T10:00:00',
            'content' => (object)['rendered' => '<p>Summary text</p>'],
        ];

        // An error object (e.g. already cached), a list of strings, and malformed items: nothing to render.
        foreach (
            [
                (object)['code' => 'rest_no_route', 'message' => 'No route was found.'],
                ['code', 'message'],
                [(object)['link' => 'https://example.com/'], (object)['title' => 'string']],
                'unexpected',
            ] as $posts
        ) {
            $this->assertStringContainsString('Error fetching feed', $this->renderRest($widget, $posts));
        }

        // Valid posts render; invalid ones in the same list are skipped.
        $html = $this->renderRest($widget, ['oops', $post, (object)['link' => 42]]);
        $this->assertSame(1, substr_count($html, 'class="rsswidget"'));
        $this->assertStringContainsString('utm_campaign=rest_widget', $html);
        $this->assertStringContainsString('Hello &lt;b&gt;world&lt;/b&gt;', $html);
        $this->assertStringContainsString('<div class="rssSummary">Summary text</div>', $html);
        $this->assertStringContainsString('class="rss-date"', $html);

        // Missing optional fields (date, content) don't warn.
        $minimal = (object)['link' => 'https://example.com/a/', 'title' => (object)['rendered' => 'Minimal']];
        $html = $this->renderRest($widget, [$minimal]);
        $this->assertStringContainsString('Minimal', $html);
        $this->assertStringNotContainsString('class="rss-date"', $html);
    }

    public function testSkipsTheWidgetWhenTheAllowFilterReturnsFalse(): void
    {
        $widget = new DashboardWidget([
            Widget::FEED_URL => 'https://example.com/feed',
            Widget::WIDGET_ID => 'blocked_widget',
            Widget::WIDGET_NAME => 'Blocked',
        ]);
        add_filter('thefrosty/wp_utilities/blocked_widget/dashboard_allowed', static fn(): bool => false);
        $widget->addHooks();
        do_action('load-index.php');
        do_action('wp_dashboard_setup');

        $this->assertArrayNotHasKey(
            'blocked_widget',
            $GLOBALS['wp_meta_boxes']['dashboard']['normal']['core'] ?? []
        );
    }

    private function restWidget(string $url): DashboardWidget
    {
        return new DashboardWidget([
            Widget::FEED_URL => $url,
            Widget::WIDGET_ID => 'rest_widget',
            Widget::WIDGET_NAME => 'REST',
        ]);
    }

    private function renderRest(DashboardWidget $widget, mixed $posts = null): string
    {
        $data = ['instance' => $widget];
        if ($posts !== null) {
            $data['posts'] = $posts;
        }

        return new View()->retrieve('dashboard-widget/rest', $data);
    }
}
