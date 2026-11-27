<?php

declare(strict_types=1);

use TheFrosty\WpUtilities\Api\WpRemote;
use TheFrosty\WpUtilities\WpAdmin\DashboardWidget;

if (!isset($instance) || !$instance instanceof DashboardWidget) {
    throw new InvalidArgumentException(
        sprintf('Missing variable `$instance`, must be an instance of %s', DashboardWidget::class)
    );
}

// Pass the widget ID to the template (outside `DashboardWidget`).
$widgetId = $instance->getWidget()->getWidgetId();
$wpRemote = new class {
    use WpRemote;
};
$posts ??= $wpRemote->retrieveBodyCached($instance->getWidget()->getFeedUrl(), WEEK_IN_SECONDS);
$renderContent ??= true; // Pass false to disable rendering the widget content on the first key.

/*
 * Only a list of REST API post objects can be rendered. An error response (e.g. a 404 decoded to
 * `{"code": "rest_no_route", "message": …}`) is an object, and iterating it yields strings.
 */
$items = is_array($posts) && array_is_list($posts) ? array_values(array_filter(
    $posts,
    static fn(mixed $item): bool => is_object($item) &&
        isset($item->link, $item->title) &&
        is_string($item->link) &&
        is_object($item->title) &&
        is_string($item->title->rendered ?? null)
)) : [];

$content = '';
if ($items === []) {
    $content .= '<li>' . esc_html__('Error fetching feed', 'wp-utilities') . '</li>';
} else {
    foreach ($items as $index => $item) {
        $link = add_query_arg([
            'utm_medium' => 'wpadmin_dashboard',
            'utm_term' => 'newsitem',
            'utm_campaign' => $widgetId,
        ], $item->link);
        $content .= '<li>';
        $content .= sprintf(
            '<a class="rsswidget" href="%1$s">%2$s</a>',
            esc_url($link),
            esc_html($item->title->rendered)
        );

        if ($index === 0 && $renderContent) {
            $timestamp = is_string($item->date ?? null) ? strtotime($item->date) : false;
            if ($timestamp !== false) {
                $content .= '&nbsp;&nbsp;&nbsp;<span class="rss-date">' .
                    esc_html(date_i18n(get_option('date_format'), $timestamp)) . '</span>';
            }
            $summary = is_string($item->content->rendered ?? null) ? $item->content->rendered : '';
            $content .= '<div class="rssSummary">' .
                esc_html(wp_strip_all_tags(wp_trim_words($summary, 28))) . '</div>';
        }
        $content .= '</li>';
    }
}

echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped above.
