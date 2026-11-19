<?php

declare(strict_types=1);

use SimplePie\Item;
use TheFrosty\WpUtilities\WpAdmin\Dashboard\SimplePie;
use TheFrosty\WpUtilities\WpAdmin\DashboardWidget;

if (!isset($instance) || !$instance instanceof DashboardWidget) {
    throw new InvalidArgumentException(
        sprintf('Missing variable `$instance`, must be an instance of %s', DashboardWidget::class)
    );
}

$simplePie = new class {
    use SimplePie;
};
// This view is included from `views/dashboard-widget.php`, where `$this` is the View, not the DashboardWidget.
$posts = $simplePie->getFeedItems(1, $instance->getWidget()->getFeedUrl());
$count = 0;

$content = '';
if (empty($posts)) {
    $content .= '<li>' . esc_html__('Error fetching feed', 'wp-utilities') . '</li>';
} else {
    foreach ($posts as $item) {
        if (!($item instanceof Item)) {
            continue;
        }

        $count++;
        $content .= '<li>';
        $link = add_query_arg([
            'utm_medium' => 'wpadmin_dashboard',
            'utm_term' => 'newsitem',
            'utm_campaign' => $instance->getWidget()->getWidgetId(),
        ], (string)$item->get_permalink());
        $content .= sprintf(
            '<a class="rsswidget" href="%1$s">%2$s</a>',
            esc_url($link),
            esc_html((string)$item->get_title())
        );

        if ($count === 1) {
            $content .= '&nbsp;&nbsp;&nbsp;<span class="rss-date">' .
                esc_html((string)$item->get_date(get_option('date_format'))) . '</span>';
            $content .= '<div class="rssSummary">' .
                esc_html(wp_strip_all_tags(wp_trim_words((string)$item->get_description(), 28))) . '</div>';
        }
        $content .= '</li>';
    }
}

echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped above.
