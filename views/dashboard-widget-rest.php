<?php declare(strict_types=1);

$wpRemote = new class {

    use TheFrosty\WpUtilities\Api\WpRemote;
};
/** @var $this TheFrosty\WpUtilities\WpAdmin\DashboardWidget */
$posts ??= $wpRemote->retrieveBodyCached($this->getWidget()->getFeedUrl(), DAY_IN_SECONDS);
static $count;

$content = '';
if (empty($posts)) {
    $content .= '<li>' . __('Error fetching feed') . '</li>';
} else {
    foreach ($posts as $item) {
        $count++;
        $content .= '<li>';
        $content .= '<a class="rsswidget" href="' . esc_url(add_query_arg([
                'utm_medium' => 'wpadmin_dashboard',
                'utm_term' => 'newsitem',
                'utm_campaign' => $this->getWidget()->getWidgetId(),
            ], $item->guid->rendered)) . '">' . esc_html($item->title->rendered) . '</a>';

        if ($count === 1) {
            $content .= '&nbsp;&nbsp;&nbsp;<span class="rss-date">' .
                date_i18n(get_option('date_format'), strtotime($item->date)) . '</span>';
            $content .= '<div class="rssSummary">' . strip_tags(wp_trim_words($item->content->rendered, 28)) . '</div>';
        }
        $content .= '</li>';
    }
    unset($count);
}

echo $content;
