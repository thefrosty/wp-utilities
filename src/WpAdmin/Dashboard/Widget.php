<?php declare(strict_types=1);

namespace TheFrosty\WpUtilities\WpAdmin\Dashboard;

use TheFrosty\WpUtilities\Models\BaseModel;

/**
 * Class Widget
 *
 * @package TheFrosty\WpUtilities\WpAdmin\Dashboard
 */
class Widget extends BaseModel
{

    /** @var string $feed_url */
    private $feed_url;

    /** @var string $widget_id */
    private $widget_id;

    /** @var string $widget_name */
    private $widget_name;

    /**
     * Set the feed URL.
     * @param string $url
     */
    public function setFeedUrl(string $url)
    {
        $this->feed_url = $url;
    }

    /**
     * Get the feed URL.
     * @return string
     */
    public function getFeedUrl() : string
    {
        return $this->feed_url;
    }

    /**
     * Set the widget ID.
     * @param string $widget_id
     */
    public function setWidgetId(string $widget_id)
    {
        $this->widget_id = $widget_id;
    }

    /**
     * Get the widget ID.
     * @return string
     */
    public function getWidgetId() : string
    {
        return $this->widget_id;
    }

    /**
     * Set the widget name.
     * @param string $widget_name
     */
    public function setWidgetName(string $widget_name)
    {
        $this->widget_name = $widget_name;
    }

    /**
     * Get the widget name.
     * @return string
     */
    public function getWidgetName() : string
    {
        return $this->widget_name;
    }
}
