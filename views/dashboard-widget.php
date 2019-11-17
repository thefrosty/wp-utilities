<?php

use TheFrosty\WpUtilities\WpAdmin\DashboardWidget;

/**
 * DashboardWidget object.
 * @var $this DashboardWidget
 */
if (!($this instanceof DashboardWidget)) {
    wp_die(sprintf('Please don\'t load this file outside of <code>%s.</code>', esc_attr(DashboardWidget::class)));
}
static $count;

$rss_items = $this->getFeedItems(1, $this->getWidget()->getFeedUrl());

$content = '<div class="rss-widget"><ul>';

if (empty($rss_items)) {
    $content .= '<li>' . __('Error fetching feed') . '</li>';
} else {
    foreach ($rss_items as $key => $item) {
        if (!($item instanceof SimplePie_Item)) {
            continue;
        }

        $count++;
        $content .= '<li>';
        $content .= '<a class="rsswidget" href="' . esc_url(add_query_arg([
                'utm_medium' => 'wpadmin_dashboard',
                'utm_term' => 'newsitem',
                'utm_campaign' => $this->getWidget()->getWidgetId(),
            ], $item->get_permalink())) . '">' . esc_html($item->get_title()) . '</a>';

        if ($count === 1) {
            $content .= '&nbsp;&nbsp;&nbsp;<span class="rss-date">' . $item->get_date(get_option('date_format')) . '</span>';
            $content .= '<div class="rssSummary">' . strip_tags(wp_trim_words($item->get_description(), 28)) . '</div>';
        }
        $content .= '</li>';
    }
    unset($count);
}
$content .= '</ul></div>';

echo $content;