<?php

namespace TheFrosty\WP\Utils\WpAdmin\Dashboard;

use TheFrosty\WP\Utils\Models\BaseModel;

/**
 * Class Widget
 *
 * @package TheFrosty\WP\Utils\WpAdmin\Dashboard
 */
class Widget extends BaseModel {

    /** @var string $feed_url */
    private $feed_url;

    /** @var string $widget_id */
    private $widget_id;

    /** @var string $widget_name */
    private $widget_name;

    /**
     * @param string $url
     */
    public function setFeedUrl( string $url ) {
        $this->feed_url = $url;
    }

    /**
     * @return string
     */
    public function getFeedUrl(): string {
        return $this->feed_url;
    }

    /**
     * @param string $widget_id
     */
    public function setWidgetId( string $widget_id ) {
        $this->widget_id = $widget_id;
    }

    /**
     * @return string
     */
    public function getWidgetId(): string {
        return $this->widget_id;
    }

    /**
     * @param string $widget_name
     */
    public function setWidgetName( string $widget_name ) {
        $this->widget_name = $widget_name;
    }

    /**
     * @return string
     */
    public function getWidgetName(): string {
        return $this->widget_name;
    }
}