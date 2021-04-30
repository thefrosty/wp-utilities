<?php declare(strict_types=1);

namespace TheFrosty\WpUtilities\WpAdmin;

use TheFrosty\WpUtilities\Plugin\HooksTrait;
use TheFrosty\WpUtilities\Plugin\WpHooksInterface;
use TheFrosty\WpUtilities\WpAdmin\Dashboard\Widget;

/**
 * Class DashboardWidget
 *
 * @package TheFrosty\WpUtilities\WpAdmin
 */
class DashboardWidget implements WpHooksInterface
{
    use HooksTrait;

    const OBJECT_NAME = 'DashboardWidget';

    /** @var array $args */
    private array $args;

    /** @var Widget $widget */
    private Widget $widget;

    /**
     * DashboardWidget constructor.
     *
     * @param array $args
     */
    public function __construct(array $args)
    {
        $this->args = $args;
    }

    /**
     * Add class hooks.
     */
    public function addHooks(): void
    {
        $this->addAction('load-index.php', [$this, 'loadIndexPhp']);
    }

    /**
     * Load additional hooks for this class.
     */
    protected function loadIndexPhp(): void
    {
        $this->setWidget($this->args);
        $this->addAction('wp_dashboard_setup', [$this, 'addDashboardWidget']);
    }

    /**
     * Add Dashboard widget
     */
    protected function addDashboardWidget(): void
    {
        if (!$this->isDashboardAllowed()) {
            return;
        }

        \wp_add_dashboard_widget(
            $this->getWidget()->getWidgetId(),
            $this->getWidget()->getWidgetName(),
            function () {
                $this->renderDashboardWidget();
            }
        );
    }

    /**
     * Fetch RSS items from the feed.
     *
     * @param int $max Number of items to fetch.
     * @param string $url The feed to fetch.
     *
     * @return array|\SimplePie_Item[] Empty array on failure, Array of \SimplePie_Item'a on
     *     success.
     */
    public function getFeedItems(int $max, string $url): array
    {
        if (!\function_exists('fetch_feed')) {
            include_once ABSPATH . WPINC . DIRECTORY_SEPARATOR . 'feed.php';
        }

        /**
         * @param string $url
         *
         * @return array|\SimplePie
         */
        $get_pie = function (string $url) {
            $pie = \fetch_feed($url);
            // Bail if feed doesn't work
            if (!($pie instanceof \SimplePie) || \is_wp_error($pie)) {
                return [];
            }

            return $pie;
        };

        $pie = $get_pie($url);
        // Bail if feed doesn't work
        if (!($pie instanceof \SimplePie)) {
            return [];
        }

        /**
         * @param array|\SimplePie $pie
         *
         * @return array|null $get_items
         */
        $get_items = static function ($pie) use ($max) {
            return ($pie instanceof \SimplePie) ? $pie->get_items(0, $pie->get_item_quantity($max)) : [];
        };

        $pie_items = $get_items($pie);
        // If the feed was erroneous
        if (!$pie_items) {
            $md5 = \md5($url);
            \delete_transient('feed_' . $md5);
            \delete_transient('feed_mod_' . $md5);
            $pie = $get_pie($url);
            $pie_items = $get_items($pie);
        }

        return \is_array($pie_items) ? $pie_items : [];
    }

    /**
     * Return the current Widget object.
     *
     * @return Widget
     */
    public function getWidget(): Widget
    {
        return $this->widget;
    }

    /**
     * Creates in new instance of the Widget object.
     *
     * @param array $args
     */
    private function setWidget(array $args): void
    {
        $this->widget = new Widget($args);
    }

    /**
     * Render the dashboard widget
     */
    private function renderDashboardWidget(): void
    {
        include __DIR__ . '/../../views/dashboard-widget.php';
    }

    /**
     * Check if the dashboard widget is allowed.
     *
     * @return bool
     */
    private function isDashboardAllowed(): bool
    {
        $allowed = \apply_filters(\sprintf(
            'thefrosty_wp_util_%s_dashboard_allowed',
            \sanitize_key($this->getWidget()->getWidgetId())
        ), true);

        return $allowed === true;
    }
}
