<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\PostMeta;

use TheFrosty\WpUtilities\Plugin\AbstractHookProvider;
use TheFrosty\WpUtilities\Plugin\HttpFoundationRequestInterface;
use TheFrosty\WpUtilities\Plugin\HttpFoundationRequestTrait;
use TheFrosty\WpUtilities\PostMeta\Fields\AbstractField;
use TheFrosty\WpUtilities\PostMeta\Fields\Field;
use WP_Post;
use function add_meta_box;
use function array_key_exists;
use function defined;
use function is_string;
use function wp_is_post_revision;
use function wp_nonce_field;

/**
 * Class PostMetaManager
 * @package TheFrosty\WpUtilities\PostMeta
 */
class PostMetaManager extends AbstractHookProvider implements HttpFoundationRequestInterface
{

    use HttpFoundationRequestTrait;

    /**
     * @var AbstractField[] $fields
     */
    protected array $fields = [];

    protected ?WP_Post $post = null;

    public function __construct(protected string $name, protected string $title, protected array $types = ['post'])
    {
    }

    public function addHooks(): void
    {
        $this->addAction('add_meta_boxes', [$this, 'addMetaBox']);
        $this->addAction('save_post', [$this, 'save'], 10, 2);
    }

    /**
     * Register the meta box.
     * @param string $post_type
     */
    protected function addMetaBox(string $post_type): void
    {
        if (!in_array($post_type, $this->types, true)) {
            return;
        }

        add_meta_box(
            $this->name,
            $this->title,
            function (?WP_Post $post): void {
                $this->setPost($post);
                $this->render();
            },
            $this->types,
        );
    }

    /**
     * Save_post handler.
     * @param int $post_id
     * @param WP_Post $post
     */
    protected function save(int $post_id, WP_Post $post): void
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
     * Render fields' input elements.
     */
    protected function render(): void
    {
        wp_nonce_field($this->name, $this->name);
        foreach ($this->fields() as $field) {
            $field->render();
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

        $value = $field->sanitize($this->getRequest()->request->get($field->getId()));
        update_post_meta($this->post->ID, $field->getId(), $value);
    }

    /**
     * Relate a field to the post meta box.
     * @param string $id
     * @param array|string $object_type
     */
    public function addField(string $id, array|string $object_type): void
    {
        if (is_string($object_type)) {
            $object_type = (array)$object_type;
        }
        foreach ($object_type as $type) {
            $field = FieldsRegistrar::get($id, $type);
            if (!$field || array_key_exists($id, $this->fields)) {
                continue;
            }
            $field->manager = $this;
            $this->fields[$id] = $field;
        }
    }

    /**
     * Get field objects related to the post meta box.
     * @return AbstractField[]
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
     * @param AbstractField $field Field object.
     * @return bool Authorization yay or nay.
     */
    public function authorization(AbstractField $field): bool
    {
        return current_user_can('edit_post_meta', $this->post->ID, $field->getId());
    }

    /**
     * The object's data is stored within this UI/form handler object. This keeps all business logic flowing through
     * the manager by default, and doesn't require customizing a field for each different object type. Business logic
     * can easily be overridden at the Field object level.
     * @param WP_Post|null $post
     */
    private function setPost(?WP_Post $post = null): void
    {
        $this->post = $post;
    }
}
