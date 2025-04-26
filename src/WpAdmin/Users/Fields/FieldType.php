<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\WpAdmin\Users\Fields;

use TheFrosty\WpUtilities\WpAdmin\Users\Models\UserMetaField;
use WP_User;
use function checked;
use function esc_attr;
use function sprintf;
use function wp_kses_post;

/**
 * Trait FieldType
 * @package TheFrosty\WpUtilities\WpAdmin\Users\Fields
 */
trait FieldType
{

    /**
     * Build an input checkbox.
     * @param UserMetaField $field
     * @param WP_User $user
     * @return string
     */
    public function checkbox(UserMetaField $field, WP_User $user): string
    {
        return sprintf(
            '<input type="hidden" name="%2$s" value="off">
<input type="%1$s" name="%2$s" id="wp_utilities_%2$s" value="1" class="regular-text"%3$s>%4$s',
            esc_attr(Type::CHECKBOX->value),
            esc_attr($field->getName()),
            checked(UserMetaFieldRegistrar::getUserMeta($user->ID, $field->getName()), '1', false),
            $this->getDescription($field),
        );
    }

    /**
     * Build an input text.
     * @param UserMetaField $field
     * @param WP_User $user
     * @return string
     */
    public function text(UserMetaField $field, WP_User $user): string
    {
        return sprintf(
            '<input type="%1$s" name="%2$s" id="wp_utilities_%2$s" value="%3$s" class="regular-text">%4$s',
            esc_attr(Type::TEXT->value),
            esc_attr($field->getName()),
            esc_attr(UserMetaFieldRegistrar::getUserMeta($user->ID, $field->getName())),
            $this->getDescription($field),
        );
    }

    /**
     * Build the field description.
     * @param UserMetaField $field
     * @return string
     */
    private function getDescription(UserMetaField $field): string
    {
        if (!$field->getDescription()) {
            return '';
        }

        return sprintf('<span class="description">%s</span>', wp_kses_post($field->getDescription()));
    }
}
