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
static $count;

$content = '';
if (empty($posts)) {
    $content .= '<li>' . __('Error fetching feed') . '</li>'; // phpcs:ignore
} else {
    foreach ($posts as $item) {
        $count++;
        $content .= '<li>';
        $content .= '<a class="rsswidget" href="' . esc_url(add_query_arg([
            'utm_medium' => 'wpadmin_dashboard',
            'utm_term' => 'newsitem',
            'utm_campaign' => $widgetId,
        ], $item->link)) . '">' . esc_html($item->title->rendered) . '</a>';

        if ($count === 1 && $renderContent) {
            $content .= '&nbsp;&nbsp;&nbsp;<span class="rss-date">' .
                date_i18n(get_option('date_format'), strtotime($item->date)) . '</span>';
            $content .= '<div class="rssSummary">' .
                strip_tags(wp_trim_words($item->content->rendered, 28)) . '</div>';
        }
        $content .= '</li>';
    }
    unset($count);
}

echo $content;
