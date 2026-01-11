<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\PostMeta;

use RuntimeException;
use TheFrosty\WpUtilities\Api\Validator\Rules\Required;
use TheFrosty\WpUtilities\Api\Validator\Validator;
use TheFrosty\WpUtilities\PostMeta\Fields\AbstractField;
use TheFrosty\WpUtilities\PostMeta\Fields\Text;

/**
 * Class FieldManager
 * @package TheFrosty\WpUtilities\PostMeta
 */
class FieldsRegistrar
{

    public static array $fields;

    /**
     * Field registration.
     * @param array $args
     * @param string $object_type
     * @throws \TheFrosty\WpUtilities\Api\Validator\Exceptions\ValidationFailed
     */
    public static function add(array $args, string $object_type = 'post'): void
    {
        $defaults = [
            'id' => null, // Unique identifier.
            'field' => Text::class,
            'object_type' => $object_type,
            'types' => null, // Post Types (post, page, user, etc.) this field applies to.
        ];
        $args = wp_parse_args($args, $defaults);
        self::validate($args);
        self::register($args);
    }

    public static function all(string $object_type): ?array
    {
        return self::$fields[$object_type] ?? null;
    }

    public static function get(string $id, string $type, string $object_type = 'post'): ?AbstractField
    {
        return self::$fields[$object_type][$type][$id] ?? null;
    }

    /**
     * Internally store field data.
     * @param array $args
     * @type string $id Unique identifier.
     * @type string $field Fully qualified class name.
     * @type string $object_type WordPress objects the field applies to.
     * @type array $types WordPress objects the field applies to.
     * @type callback $authorization (Optional) Authorization.
     * @type callback $sanitization (Optional) Sanitization.
     * @throws RuntimeException
     */
    protected static function register(array $args): void
    {
        foreach ($args['types'] as $type) {
            if ($args['field'] instanceof AbstractField) {
                throw new RuntimeException('The field arg must implement `AbstractField`');
            }
            if (empty(self::$fields[$type])) {
                self::$fields[$type] = [];
            }
            $field = $args['field'];
            self::$fields[$args['object_type']][$type][$args['id']] = new $field($args);
        }
    }

    /**
     * Validate the field args.
     * @param array $args
     * @throws \TheFrosty\WpUtilities\Api\Validator\Exceptions\ValidationFailed
     */
    protected static function validate(array $args): void
    {
        $validator = Validator::getInstance();
        $rules = [
            'id' => [Required::class],
            'field' => [Required::class],
            'object_type' => [Required::class],
            'types' => [Required::class],
        ];
        $validator->validate($args, $rules);
    }
}
