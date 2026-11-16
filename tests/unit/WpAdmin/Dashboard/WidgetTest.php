<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\WpAdmin\Dashboard;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use TheFrosty\WpUtilities\Tests\Plugin\Framework\TestCase;
use TheFrosty\WpUtilities\WpAdmin\Dashboard\Widget;

/**
 * Class WidgetTest
 * @package TheFrosty\WpUtilities\Tests\WpAdmin\Dashboard
 */
#[CoversClass(Widget::class)]
#[Group('wp-admin')]
class WidgetTest extends TestCase
{
    public function testPopulatesWidgetFieldsAndIgnoresAnUnknownType(): void
    {
        $widget = new Widget([
            Widget::FEED_URL => 'https://example.com/feed',
            Widget::TYPE => 'nope',
            Widget::WIDGET_ID => 'example_widget',
            Widget::WIDGET_NAME => 'Example',
        ]);

        $this->assertSame('https://example.com/feed', $widget->getFeedUrl());
        $this->assertSame(Widget::TYPE_REST, $widget->getType());
        $this->assertSame('example_widget', $widget->getWidgetId());
        $this->assertSame('Example', $widget->getWidgetName());

        $rss = new Widget([
            Widget::FEED_URL => 'https://example.com/rss',
            Widget::TYPE => Widget::TYPE_RSS,
            Widget::WIDGET_ID => 'rss_widget',
            Widget::WIDGET_NAME => 'RSS',
        ]);
        $this->assertSame(Widget::TYPE_RSS, $rss->getType());
    }
}
