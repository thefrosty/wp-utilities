<?php declare(strict_types=1);

namespace TheFrosty\WpUtilities\WpAdmin\Dashboard;

use TheFrosty\WpUtilities\Models\BaseModel;
use function in_array;
use function strtolower;

/**
 * Class Widget
 *
 * @package TheFrosty\WpUtilities\WpAdmin\Dashboard
 */
class Widget extends BaseModel
{

    public const FEED_URL = 'feed_url';
    public const TYPE = 'type';
    public const TYPE_REST = 'rest';
    public const TYPE_RSS = 'rss';
    public const WIDGET_ID = 'widget_id';
    public const WIDGET_NAME = 'widget_name';

    /** @var string $feed_url */
    private string $feed_url;

    /** @var string $type */
    private string $type = self::TYPE_REST;

    /** @var string $widget_id */
    private string $widget_id;

    /** @var string $widget_name */
    private string $widget_name;

    /**
     * Set the feed URL.
     * @param string $url
     */
    protected function setFeedUrl(string $url): void
    {
        $this->feed_url = $url;
    }

    /**
     * Get the feed URL.
     * @return string
     */
    public function getFeedUrl(): string
    {
        return $this->feed_url;
    }

    /**
     * Set the widget ID.
     * @param string $widget_id
     */
    protected function setWidgetId(string $widget_id): void
    {
        $this->widget_id = $widget_id;
    }

    /**
     * Get the request type.
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Set the request type.
     * @param string $type
     */
    protected function setType(string $type): void
    {
        if (!in_array(strtolower($type), [self::TYPE_REST, self::TYPE_RSS], true)) {
            return;
        }
        $this->type = $type;
    }

    /**
     * Get the widget ID.
     * @return string
     */
    public function getWidgetId(): string
    {
        return $this->widget_id;
    }

    /**
     * Set the widget name.
     * @param string $widget_name
     */
    protected function setWidgetName(string $widget_name): void
    {
        $this->widget_name = $widget_name;
    }

    /**
     * Get the widget name.
     * @return string
     */
    public function getWidgetName(): string
    {
        return $this->widget_name;
    }
}
