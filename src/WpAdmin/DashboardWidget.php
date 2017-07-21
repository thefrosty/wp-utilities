<?php

namespace TheFrosty\WP\Utils\WpAdmin;

use TheFrosty\WP\Utils\WpAdmin\Dashboard\Widget;
use TheFrosty\WP\Utils\WpHooksInterface;

/**
 * Class DashboardWidget
 *
 * @package TheFrosty\WP\Utils\WpAdmin
 */
class DashboardWidget implements WpHooksInterface {

    const OBJECT_NAME = 'DashboardWidget';

    /** @var  array $widget */
    private $args = [];

    /** @var  Widget $widget */
    private $widget;

    /**
     * DashboardWidget constructor.
     *
     * @param array $args
     */
    public function __construct( array $args ) {
        $this->args = $args;
    }

    /**
     * Add class hooks.
     */
    public function addHooks() {
        add_action( 'load-index.php', [ $this, 'loadIndexPhp' ] );
    }

    /**
     * Load additional hooks for this class.
     */
    public function loadIndexPhp() {
        $this->setWidget( $this->args );
        add_action( 'wp_dashboard_setup', [ $this, 'addDashboardWidget' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueueScripts' ] );
    }

    /**
     * Add Dashboard widget
     */
    public function addDashboardWidget() {
        if ( ! $this->isDashboardAllowed() ) {
            return;
        }

        wp_add_dashboard_widget(
            $this->getWidget()->getWidgetId(),
            $this->getWidget()->getWidgetName(),
            [ $this, 'dashboardWidgetCallback' ]
        );
    }

    /**
     * Dashboard scripts & styles
     */
    public function enqueueScripts() {
    }

    /**
     * Dashboard widget
     */
    public function dashboardWidgetCallback() {
        include __DIR__ . '/../../views/dashboard-widget.php';
    }

    /**
     * Fetch RSS items from the feed.
     *
     * @param int $max Number of items to fetch.
     * @param string $url The feed to fetch.
     *
     * @return array|\SimplePie_Item[] Empty array on failure, Array of \SimplePie_Item'a on success.
     */
    public function getFeedItems( int $max, string $url ): array {
        if ( ! function_exists( 'fetch_feed' ) ) {
            include_once ABSPATH . WPINC . DIRECTORY_SEPARATOR . 'feed.php';
        }

        /**
         * @param string $url
         *
         * @return array|\SimplePie
         */
        $get_pie = function( string $url ) {
            $pie = fetch_feed( $url );
            // Bail if feed doesn't work
            if ( ! ( $pie instanceof \SimplePie ) || is_wp_error( $pie ) ) {
                return [];
            }

            return $pie;
        };

        $pie = $get_pie( $url );
        // Bail if feed doesn't work
        if ( ! ( $pie instanceof \SimplePie ) ) {
            return [];
        }

        /**
         * @param array|\SimplePie $pie
         *
         * @return array|null $get_items
         */
        $get_items = function( $pie ) use ( $max ) {
            return ( $pie instanceof \SimplePie ) ? $pie->get_items( 0, $pie->get_item_quantity( $max ) ) : [];
        };

        $pie_items = $get_items( $pie );
        // If the feed was erroneous
        if ( ! $pie_items ) {
            $md5 = md5( $url );
            delete_transient( 'feed_' . $md5 );
            delete_transient( 'feed_mod_' . $md5 );
            $pie = $get_pie( $url );
            $pie_items = $get_items( $pie );
        }

        return is_array( $pie_items ) ? $pie_items : [];
    }

    /**
     * Return the current Widget object.
     *
     * @return Widget
     */
    public function getWidget(): Widget {
        return $this->widget;
    }

    /**
     * Creates in new instance of the Widget object.
     *
     * @param array $args
     */
    private function setWidget( array $args ) {
        $this->widget = new Widget( $args );
    }

    /**
     * Check if the dashboard widget is allowed.
     *
     * @return bool
     */
    private function isDashboardAllowed(): bool {
        $id = sanitize_key( $this->getWidget()->getWidgetId() );

        return ( apply_filters( sprintf( 'thefrosty_wp_util_%s_dashboard_allowed', $id ), true ) === true );
    }
}
