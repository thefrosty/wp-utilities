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
use TheFrosty\WpUtilities\WpAdmin\Dashboard\Widget;
use TheFrosty\WpUtilities\WpAdmin\DashboardWidget;
use WP_Error;

/**
 * Class DashboardWidgetTest
 * @package TheFrosty\WpUtilities\Tests\WpAdmin
 */
#[CoversClass(DashboardWidget::class)]
#[UsesClass(Plugin::class)]
#[UsesClass(View::class)]
#[UsesClass(Widget::class)]
#[UsesTrait(Hash::class)]
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
}
