<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Integration\CarbonFields;

use Carbon_Fields\Field\Checkbox_Field;
use Carbon_Fields\Field\Field;

/**
 * Trait FieldsFactory
 * @package TheFrosty\WpUtilities\Integration\CarbonFields
 */
trait FieldsFactory
{

    /** @var array Field[] */
    protected static array $fields = [];

    public function getName(string $name): string
    {
        if (isset(self::$fields[$name]) && self::$fields[$name] instanceof Field) {
            return self::$fields[$name]->get_name();
        }

        return $name;
    }

    /**
     * Create a checkbox field.
     * @param string $name
     * @param string $label
     * @return Checkbox_Field
     */
    protected function createCheckboxField(string $name, string $label): Field
    {
        $field = Checkbox_Field::factory(FieldsInterface::CHECKBOX, $name, $label);
        self::$fields[$name] = $field;
        return $field;
    }
}
