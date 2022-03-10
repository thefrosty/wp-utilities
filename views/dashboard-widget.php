<?php declare(strict_types=1);

use TheFrosty\WpUtilities\WpAdmin\Dashboard\Widget;
use TheFrosty\WpUtilities\WpAdmin\DashboardWidget;

/**
 * DashboardWidget object.
 * @var $this DashboardWidget
 */
if (!($this instanceof DashboardWidget)) {
    wp_die(sprintf('Please don\'t load this file outside of <code>%s.</code>', esc_attr(DashboardWidget::class)));
}

echo '<div class="rss-widget"><ul>';
switch ($this->getWidget()->getType()) {
    case Widget::TYPE_REST:
        include __DIR__ . '/dashboard-widget-rest.php';
        break;
    case Widget::TYPE_RSS:
        include __DIR__ . '/dashboard-widget-rss.php';
        break;
}
echo '</ul></div>';
