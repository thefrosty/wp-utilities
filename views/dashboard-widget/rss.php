<?php declare(strict_types=1);

$simplePie = new class {

    use TheFrosty\WpUtilities\WpAdmin\Dashboard\SimplePie;
};
/** @var $this TheFrosty\WpUtilities\WpAdmin\DashboardWidget */
$posts = $simplePie->getFeedItems(1, $this->getWidget()->getFeedUrl());
static $count;

$content = '';
if (empty($posts)) {
    $content .= '<li>' . __('Error fetching feed') . '</li>';
} else {
    foreach ($posts as $item) {
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
            $content .= '&nbsp;&nbsp;&nbsp;<span class="rss-date">' .
                $item->get_date(get_option('date_format')) . '</span>';
            $content .= '<div class="rssSummary">' . strip_tags(wp_trim_words($item->get_description(), 28)) . '</div>';
        }
        $content .= '</li>';
    }
    unset($count);
}

echo $content;
