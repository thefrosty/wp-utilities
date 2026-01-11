<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\PostMeta;

use BlakvGhost\PHPValidator\Validator;
use BlakvGhost\PHPValidator\ValidatorException;
use TheFrosty\WpUtilities\Api\Rules\ArrayRule;
use TheFrosty\WpUtilities\Api\Rules\CallableRule;
use TheFrosty\WpUtilities\Api\Rules\InstanceOfRule;
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
     * @throws ValidatorException
     */
    public static function add(array $args, string $object_type = 'post'): void
    {
        $defaults = [
            'authorization' => null,
            'id' => null, // Unique identifier.
            'field' => Text::class,
            'label' => '',
            'object_type' => $object_type,
            'sanitization' => 'sanitize_text_field',
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
     * @type string $id Unique field identifier.
     * @type string $field Fully qualified field class name.
     * @type string $label Field label.
     * @type string $object_type WordPress object the field applies to.
     * @type array $types WordPress objects the field applies to.
     * @type callback $authorization (Optional) Authorization.
     * @type callback $sanitization (Optional) Sanitization.
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
        }
    }

    /**
     * Validate the field args.
     * @param array $args
     * @throws ValidatorException
     */
    protected static function validate(array $args): void
    {
        $rules = [
            'id' => ['required', 'string'],
            'field' => ['required', new InstanceOfRule([AbstractField::class])],
            'object_type' => ['required', 'string'],
            'sanitization' => ['nullable', new CallableRule()],
            'types' => ['required', new ArrayRule()],
        ];
        $validator = new Validator($args, $rules);
        if (!$validator->isValid()) {
            throw new ValidatorException('');
        }
    }
}
