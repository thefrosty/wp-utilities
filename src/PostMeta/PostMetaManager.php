<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\PostMeta;

use TheFrosty\WpUtilities\PostMeta\Fields\AbstractField;
use WP_Post;
use function add_meta_box;
use function array_key_exists;
use function current_user_can;
use function defined;
use function get_post_meta;
use function is_string;
use function update_post_meta;
use function wp_is_post_revision;
use function wp_nonce_field;
use function wp_verify_nonce;

/**
 * Class PostMetaManager
 * @package TheFrosty\WpUtilities\PostMeta
 */
class PostMetaManager extends AbstractManager
{

    /**
     * PostMetaManager constructor.
     * @param string $id
     * @param string $title
     * @param array|null $fields
     * @param string $type
     */
    public function __construct(
        protected string $id,
        protected string $title,
        ?array $fields = null,
        string $type = 'post'
    ) {
        parent::__construct($fields, $type);
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
     * Default authorization callback for post meta.
     * @param AbstractField $field Field object.
     * @return bool Authorization yay or nay.
     */
    public function authorization(AbstractField $field): bool
    {
        return current_user_can('edit_post_meta', $this->post->ID, $field->getId());
    }

    /**
     * Default save method specific to a post type.
     * @param AbstractField $field
     */
    public function saveField(AbstractField $field): void
    {
        if (!isset($this->post) || !$field->authorization()) {
            return;
        }

        // Only update fields that were submitted; a missing key must not wipe the stored value.
        if (!$this->getRequest()->request->has($field->getId())) {
            return;
        }

        $value = $field->sanitize($this->getRequest()->request->get($field->getId()));
        update_post_meta($this->post->ID, $field->getId(), $value);
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
     * Register the post meta box.
     * @param WP_Post $post
     */
    protected function addMetaBox(WP_Post $post): void
    {
        add_meta_box($this->id, $this->title, function () use ($post): void {
            $this->post = $post;
            $this->render();
        }, $this->type);
    }

    /**
     * Save post handler.
     * @param int $post_id
     * @param WP_Post $post
     */
    protected function save(int $post_id, WP_Post $post): void
    {
        if (wp_is_post_revision($post_id)) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Only save when our meta box was submitted (protects against CSRF, and against Quick Edit, REST and
        // programmatic `wp_update_post()` calls that fire `save_post` without our fields).
        $nonce = $this->getRequest()->request->all()[$this->id] ?? null;
        if (!is_string($nonce) || !wp_verify_nonce($nonce, $this->id)) {
            return;
        }

        $this->post = $post;
        foreach ($this->fields as $field) {
            $this->saveField($field);
        }
    }

    /**
     * Render the fields.
     */
    private function render(): void
    {
        wp_nonce_field($this->id, $this->id);
        foreach ($this->fields as $field) {
            $field->render();
        }
    }
}
