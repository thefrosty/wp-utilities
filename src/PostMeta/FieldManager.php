<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\PostMeta;

use RuntimeException;
use TheFrosty\WpUtilities\PostMeta\Fields\AbstractField;
use TheFrosty\WpUtilities\PostMeta\Fields\Text;

/**
 * Class FieldManager
 * @package TheFrosty\WpUtilities\PostMeta
 */
class FieldManager
{

    public static array $fields;

    /**
     * Field registration.
     * Abstracted from the UI view class, so that the field's business logic
     * and data describing it can be accessed independently.
     * Data about fields is stored in a nested array.
     * @param array $args
     */
    public static function addField(array $args): void
    {
        $defaults = [
            'name' => null, // Unique identifier.
            'object_types' => [], // WP objects (post, page, user, etc.) this field applies to.
            'field' => Text::class,
        ];
        $args = wp_parse_args($args, $defaults);
        self::registerField($args);
    }

    public static function getField(string $object_type, string $field_name): ?AbstractField
    {
        return self::$fields[$object_type][$field_name] ?? null;
    }

    public static function getFields(string $object_type): ?array
    {
        return self::$fields[$object_type] ?? null;
    }

    /**
     * Internally store field data.
     * @param array $args
     * @type string $name Unique identifier.
     * @type array $object_types WordPress objects the field applies to.
     * @type string $field Fully qualified class name.
     * @type callback $authorization
     * @type callback $sanitization
     * @throws RuntimeException
     */
    protected static function registerField(array $args): void
    {
        foreach ($args['object_types'] as $object_type) {
            if ($args['field'] instanceof AbstractField) {
                throw new RuntimeException('The field arg must implement `AbstractField`');
            }
            $classname = $args['field'];
            self::$fields[$object_type][$args['name']] = new $classname($args);
        }
    }
}
