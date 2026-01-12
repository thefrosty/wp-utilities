<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\PostMeta;

use BlakvGhost\PHPValidator\Validator;
use BlakvGhost\PHPValidator\ValidatorException;
use TheFrosty\WpUtilities\Api\ValidationRules\InstanceOfRule;
use TheFrosty\WpUtilities\Api\ValidationRules\IsArray;
use TheFrosty\WpUtilities\Api\ValidationRules\IsCallable;
use TheFrosty\WpUtilities\PostMeta\Fields\AbstractField;
use TheFrosty\WpUtilities\PostMeta\Fields\Text;
use function register_post_meta;

/**
 * Class FieldManager
 * @package TheFrosty\WpUtilities\PostMeta
 */
class FieldsRegistrar
{

    public static array $fields;

    /**
     * Field registration.
     * @param array $args Data used to describe the meta key when registered. See
     *  {@see register_meta()} for a list of supported arguments.
     * @param string $type if the object type is "post", the post type. If left empty, the meta key will be registered
     *     on the entire object type. Default empty.
     * @type string $type The type of data associated with this meta key.
     * @param string $object_type Type of object metadata is for. Accepts 'blog', 'post', 'comment', 'term',
     *  'user', or any other object type with an associated meta table.
     * @throws ValidatorException
     */
    public static function add(array $args, string $type, string $object_type = 'post'): void
    {
        $defaults = [
            'auth_callback' => null,
            'default' => '',
            'description' => '',
            'field' => Text::class,
            'id' => null, // Unique identifier.
            'label' => '',
            'object_type' => $object_type,
            'single' => false,
            'sanitize_callback' => 'sanitize_text_field',
            'show_in_rest' => false,
            'type' => 'string', // Valid values are 'string', 'boolean', 'integer', 'number', 'array', and 'object'.
            'types' => [$type], // Post Types (post, page, user, etc.) this field applies to.
            'revisions_enabled' => false,
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
     * Internally store and register field data.
     * @param array $args See {@see register_meta()} for a list of supported arguments.
     */
    protected static function register(array $args): void
    {
        foreach ($args['types'] as $type) {
            if (empty(self::$fields[$args['object_type']])) {
                self::$fields[$args['object_type']] = [];
            }
            if (empty(self::$fields[$args['object_type']][$type])) {
                self::$fields[$args['object_type']][$type] = [];
            }
            $field = $args['field'];
            self::$fields[$args['object_type']][$type][$args['id']] = new $field($args);
            register_post_meta($type, $args['id'], $args);
        }
    }

    /**
     * Validate the field args.
     * @param array $args See {@see register_meta()} for a list of supported arguments.
     * @throws ValidatorException
     */
    protected static function validate(array $args): void
    {
        $rules = [
            'description' => ['string'],
            'id' => ['required', 'string'],
            'field' => ['required', new InstanceOfRule([AbstractField::class])],
            'sanitize_callback' => ['nullable', new IsCallable()],
            'single' => ['bool'],
            'type' => ['required', 'in:string,boolean,integer,number,array,object'],
            'types' => ['required', new IsArray()],
        ];
        $validator = new Validator($args, $rules);
        if (!$validator->isValid()) {
            throw new ValidatorException(implode("\n", $validator->getErrors()));
        }
    }
}
