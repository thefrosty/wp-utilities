<?php declare(strict_types=1);

namespace TheFrosty\WpUtilities\Models;

use Exception;
use function date_create;
use function in_array;
use function is_array;
use function is_null;
use function is_object;
use function method_exists;

/**
 * Class BaseModel
 * @package TheFrosty\WpUtilities\Models
 */
abstract class BaseModel
{

    /**
     * BaseModel constructor.
     * @param array|null $fields
     */
    public function __construct(?array $fields = null)
    {
        if (!is_array($fields)) {
            return;
        }
        $this->populate($fields);
    }

    /**
     * Optional. Implement customized getCustomDelimiters() to return values
     * to search and replace for getMethod().
     * @return array
     */
    public function getCustomDelimiters(): array
    {
        return [];
    }

    /**
     * Optional method to get a model as an array.
     * Default implementation is to engage fields listed in getSerializableFields().
     * You should implement customized toArray() if you are using logic different from described in
     * getSerializableFields() in child classes.
     * @return array
     * @throws Exception
     */
    public function toArray(): array
    {
        if (!empty($this->getSerializableFields())) {
            $result = [];

            foreach ($this->getSerializableFields() as $field_name) {
                $method = $this->getGetMethod($field_name);
                if (!method_exists($this, $method)) {
                    continue;
                }
                $value = $this->$method();
                if (is_object($value) && method_exists($value, 'toArray')) {
                    $result[$field_name] = $value->toArray();
                    continue;
                }
                if (
                    (is_array($value) && !empty($value[0])) &&
                    (is_object($value[0]) && method_exists($value[0], 'toArray'))
                ) {
                    $result[$field_name] = $this->toArrayDeep($value);
                    continue;
                }
                $result[$field_name] = $value;
            }

            return $result;
        }
        throw new Exception(
            'If you are going to use toArray() in your model you have
           to implement custom logic or return a list of fields in getSerializableFields().'
        );
    }

    /**
     * Method to convert an array of BaseModels to an array of BaseModel objects converted to an
     * array.
     *
     * This is useful in situations where you have an array of BaseModels and need to convert it
     * into an array for the purpose of sending it to the front end through WordPress' localize
     * script functionality.
     *
     * @param BaseModel[] $models
     * @return array
     * @throws Exception
     */
    public function toArrayDeep(array $models): array
    {
        $deep_array = [];
        foreach ($models as $model) {
            $deep_array[] = $model->toArray();
        }

        return $deep_array;
    }

    /**
     * Get datetime fields.
     * @return array
     */
    protected function getDateTimeFields(): array
    {
        return [];
    }

    /**
     * Get the fields to be used in toArray()
     * Field names should be in camelCase (ex. propertyName) so that getPropertyName could easily be called
     * @return array
     */
    protected function getSerializableFields(): array
    {
        return [];
    }

    /**
     * Populate model.
     * @param array $fields
     */
    protected function populate(array $fields): void
    {
        foreach ($fields as $field => $value) {
            // If field value is null we just leave it blank
            if (is_null($value)) {
                continue;
            }

            $setter_method = $this->getSetterMethod($field);
            $populate_method = $this->getPopulateMethod($field);

            // First try to proceed with custom population logic
            if (method_exists($this, $populate_method)) {
                $this->$populate_method($value);
                // If no custom logic found proceed with regular setters
            } elseif (method_exists($this, $setter_method)) {
                // Should we convert it to datetime?
                if (in_array($field, $this->getDateTimeFields(), true)) {
                    $value = date_create($value);
                }
                $this->$setter_method($value);
            }
        }
    }

    /**
     * Gets the 'get' method.
     * @param string $field
     * @return string
     */
    protected function getGetMethod(string $field): string
    {
        return $this->getMethod('get', $field);
    }

    /**
     * Gets the 'populate' method.
     * @param string $field
     * @return string
     */
    private function getPopulateMethod(string $field): string
    {
        return $this->getMethod('populate', $field);
    }

    /**
     * Gets the 'set' method.
     * @param string $field
     * @return string
     */
    private function getSetterMethod(string $field): string
    {
        return $this->getMethod('set', $field);
    }

    /**
     * Helper to get the method with prefix.
     * @param string $prefix
     * @param string $field
     * @return string
     */
    private function getMethod(string $prefix, string $field): string
    {
        $search = \array_merge(['_', '-'], $this->getCustomDelimiters());
        $delimiters = '_-';
        $delimiters .= !empty($this->getCustomDelimiters()) ? \implode('', $this->getCustomDelimiters()) : '';

        return $prefix . \str_replace($search, '', \ucwords($field, $delimiters));
    }
}
