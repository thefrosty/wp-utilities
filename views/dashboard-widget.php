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

$div_open = '<div class="rss-widget"><ul>';
$div_close = '</ul></div>';
echo $div_open;
switch ($this->getWidget()->getType()) {
    case Widget::TYPE_RSS:
        $template = __DIR__ . '/dashboard-widget/rss.php';
        break;
    case Widget::TYPE_REST:
    default:
        $template = __DIR__ . '/dashboard-widget/rest.php';
        break;
}
include $template;
echo $div_close;

/**
 * Render additional content.
 * @param string $div_open The opening div tag.
 * @param string $div_close The closing div tag.
 * @param string $template The template file to use.
 * @param Widget $widget The widget object.
 */
do_action(DashboardWidget::HOOK_NAME_RENDER, $div_open, $div_close, $template, $this->getWidget());
