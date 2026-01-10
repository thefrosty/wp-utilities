<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\PostMeta;

use TheFrosty\WpUtilities\Plugin\HttpFoundationRequestInterface;
use TheFrosty\WpUtilities\Plugin\HttpFoundationRequestTrait;
use TheFrosty\WpUtilities\PostMeta\Fields\Field;
use TheFrosty\WpUtilities\PostMeta\Fields\Render;
use WP_Post;
use function add_meta_box;
use function get_post;
use function wp_nonce_field;

/**
 * Class PostMetaManager
 * @package TheFrosty\WpUtilities\PostMeta
 */
class PostMetaManager implements HttpFoundationRequestInterface, Render
{

    use HttpFoundationRequestTrait;

    /**
     * Related fields
     * @var Field[]
     */
    protected array $fields = [];

    protected string $name = '';

    protected string $title = '';

    protected WP_Post $post;

    public function __construct($args = [])
    {
        $keys = array_keys(get_class_vars(self::class));
        foreach ($keys as $key) {
            if (isset($args[$key])) {
                $this->$key = $args[$key];
            }
        }

        if (empty($this->name)) {
            $this->name = sanitize_title($this->title);
        }

        // Register object-specific UI.
        add_action('add_meta_boxes', [$this, 'addMetaBox']);

        // Register data-submission handler.
        add_action('save_post', [$this, 'save'], 10, 2);
    }

    /**
     * Register the meta box.
     */
    public function addMetaBox(): void
    {
        add_meta_box(
            $this->name,
            $this->title,
            function (): void {
                $this->setPost(get_post());
                $this->render();
            },
            'post',
        );
    }

    /**
     * Render fields' input elements.
     */
    public function render(): void
    {
        wp_nonce_field($this->name, $this->name);
        foreach ($this->fields() as $field) {
            $field->render();
        }
    }

    /**
     * Save_post handler.
     * @param int $post_id
     * @param WP_Post $post
     */
    public function save(int $post_id, WP_Post $post): void
    {
        $this->setPost($post);

        if (wp_is_post_revision($post_id)) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        foreach ($this->fields() as $field) {
            $this->saveField($field);
        }
    }

    /**
     * Default save method specific to a post type.
     * @param Field $field
     */
    public function saveField(Field $field): void
    {
        if (!$field->authorization()) {
            return;
        }

        $value = $field->sanitize($this->getRequest()->request->get($field->getName()));
        update_post_meta($this->post->ID, $field->getName(), $value);
    }

    /**
     * Relate a field to the post meta box.
     * @param string $field_name
     */
    public function addField(string $field_name): void
    {
        $field = FieldManager::getField('post', $field_name);
        $field->manager = $this;
        $this->fields[] = $field;
    }

    /**
     * Get field objects related to the post meta box.
     */
    public function fields(): array
    {
        return $this->fields;
    }

    /**
     * Default get method specific to a post type.
     * @param string $field_name
     * @return mixed
     */
    public function value(string $field_name): mixed
    {
        return get_post_meta($this->post->ID, $field_name, true);
    }

    /**
     * Default authorization callback for post meta.
     * @param Field $field Field object.
     * @return bool Authorization yay or nay.
     */
    public function authorization(Field $field): bool
    {
        return current_user_can('edit_post_meta', $this->post->ID, $field->getName());
    }

    /**
     * The object's data is stored within this UI/form handler object.
     * This keeps all business logic flowing through the manager by default,
     * and doesn't require customizing a field for each different object
     * type. Business logic can easily be overridden at the Field object level.
     * @param WP_Post|null $post
     */
    private function setPost(?WP_Post $post = null): void
    {
        $this->post = $post;
    }
}
