<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\WpAdmin\Users;

use TheFrosty\WpUtilities\Plugin\AbstractContainerProvider;
use TheFrosty\WpUtilities\Plugin\HttpFoundationRequestInterface;
use TheFrosty\WpUtilities\Plugin\HttpFoundationRequestTrait;
use TheFrosty\WpUtilities\Utils\View;
use TheFrosty\WpUtilities\Utils\Viewable;
use TheFrosty\WpUtilities\WpAdmin\Users\Fields\Type;
use TheFrosty\WpUtilities\WpAdmin\Users\Models\UserMetaField;
use WP_User;
use function apply_filters;
use function array_filter;
use function current_action;
use function current_user_can;
use function do_action;
use function esc_html__;
use function get_user_meta;
use function is_callable;
use function is_scalar;
use function sanitize_text_field;
use function sprintf;
use function update_user_meta;
use function wp_get_current_user;

/**
 * Class Profile
 * @package TheFrosty\WpUtilities\WpAdmin\Users
 */
class Profile extends AbstractContainerProvider implements HttpFoundationRequestInterface
{

    use HttpFoundationRequestTrait;
    use Viewable;

    public const string ACTION = 'wp_utilities_user_profile';
    public const string HOOK_NAME_REGISTER_USER_META_FIELDS = self::ACTION . '_register_fields';
    public const string HOOK_NAME_USER_PROFILE_S = self::ACTION . '_%s';
    public const string NONCE_ACTION_S = 'user_%d';

    /**
     * Add class hooks.
     */
    public function addHooks(): void
    {
        foreach (['edit', 'show'] as $prefix) {
            $this->addAction("{$prefix}_user_profile", [$this, 'userProfile']);
        }

        $this->addAction('personal_options_update', [$this, 'saveProfileFields']);
        $this->addAction('edit_user_profile_update', [$this, 'saveProfileFields']);
    }

    /**
     * Get all registered UserMetaFields.
     * @return UserMetaField[]
     */
    protected function getUserMetaFields(): array
    {
        $fields = (array)apply_filters(self::HOOK_NAME_REGISTER_USER_META_FIELDS, []);
        return array_filter($fields, static fn($field): bool => $field instanceof UserMetaField);
    }

    /**
     * User profile HTML.
     * @param WP_User $user The user
     */
    protected function userProfile(WP_User $user): void
    {
        printf('<h3>%s</h3>', esc_html__('Extra Profile Fields', 'wp-utilities'));

        $fields = $this->getUserMetaFields();

        /**
         * Custom action hook to load on the user's edit/show profile.
         * @param WP_User $user The user profile object.
         * @param UserMetaField[] $fields array of field objects.
         */
        do_action(sprintf(self::HOOK_NAME_USER_PROFILE_S, current_action()), $user, $fields);

        new View()->render(
            'wp-admin/users/profile',
            [
                'current_user' => wp_get_current_user(),
                'fields' => $fields,
                'query' => $this->getRequest()->query,
                'user' => $user,
            ]
        );
    }

    /**
     * Maybe save our custom user fields.
     * @param int $user_id
     */
    protected function saveProfileFields(int $user_id): void
    {
        $fields = $this->getUserMetaFields();
        $request = $this->getRequest()->request;
        if (
            !$request->has(self::ACTION) ||
            !wp_verify_nonce($request->get(self::ACTION), sprintf(self::NONCE_ACTION_S, $user_id)) ||
            !current_user_can('edit_user', $user_id) ||
            empty($fields)
        ) {
            return;
        }

        foreach ($fields as $field) {
            // Fields hidden from the form (by their condition) must not be writable either.
            if (self::isFieldHidden($field) || !$request->has($field->getName())) {
                continue;
            }

            $value = $request->all()[$field->getName()] ?? null;
            if (!is_scalar($value)) {
                continue;
            }

            $prev_value = get_user_meta($user_id, $field->getName(), true);
            $value = self::sanitizeFieldValue($field, (string)$value);
            update_user_meta($user_id, $field->getName(), $value, $prev_value);
        }
    }

    /**
     * Is the field hidden from the current user? A field is hidden when its condition returns `true`.
     * @param UserMetaField $field
     * @return bool
     */
    public static function isFieldHidden(UserMetaField $field): bool
    {
        $condition = $field->getCondition();

        return is_callable($condition) && $condition() === true;
    }

    /**
     * Sanitize a submitted value based on the field type.
     * @param UserMetaField $field
     * @param string $value
     * @return string
     */
    private static function sanitizeFieldValue(UserMetaField $field, string $value): string
    {
        return match ($field->getType()) {
            Type::CHECKBOX => $value === '1' ? '1' : 'off',
            Type::TEXT => sanitize_text_field($value),
        };
    }
}
