<?php

namespace TheFrosty\WP\Utils\Models;

/**
 * Class BaseModel
 *
 * @package TheFrosty\WP\Utils\Models
 */
abstract class BaseModel {

    /**
     * BaseModel constructor.
     *
     * @param array $fields
     */
    public function __construct( array $fields ) {
        $this->populate( $fields );
    }

    /**
     * Optional method to get a model as an array
     * Default implementation is to engage fields listed in getSerializableFields()
     *
     * You should implement customized toArray() if you are using
     * logic different from described in getSerializableFields() in child classes
     *
     * @return array
     * @throws \Exception
     */
    public function toArray(): array {
        if ( ! empty( $this->getSerializableFields() ) ) {
            $result = [];

            foreach ( $this->getSerializableFields() as $index => $field_name ) {
                $value = $this->{'get' . ucwords( $field_name )}();
                if ( is_object( $value ) && method_exists( $value, 'toArray' ) ) {
                    $result[ $field_name ] = $value->toArray();
                } else {
                    $result[ $field_name ] = $value;
                }
            }

            return $result;
        }
        throw new \Exception(
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
     *
     * @return array
     */
    public function toArrayDeep( array $models ): array {

        $deep_array = [];
        foreach ( $models as $model ) {
            $deep_array[] = $model->toArray();
        }

        return $deep_array;
    }

    /**
     * Get datetime fields
     *
     * @return array
     */
    protected function getDateTimeFields() {
        return [];
    }

    /**
     * Get the fields to be used in toArray()
     * Field names should be in camelCase (ex. propertyName)
     * so that getPropertyName could easily be called
     *
     * @return array
     */
    protected function getSerializableFields(): array {
        return [];
    }

    /**
     * Populate model
     *
     * @param array $fields
     *
     * @return void
     */
    protected function populate( $fields ) {
        foreach ( $fields as $field => $value ) {
            // If field value is null we just leave it blank
            if ( is_null( $value ) ) {
                continue;
            }

            $setter_method = $this->getSetterMethod( $field );
            $populate_method = $this->getPopulateMethod( $field );

            // First try to proceed with custom population logic
            if ( method_exists( $this, $populate_method ) ) {
                $this->$populate_method( $value );
                // If no custom logic found proceed with regular setters
            } elseif ( method_exists( $this, $setter_method ) ) {
                // Should we convert it to datetime?
                if ( in_array( $field, $this->getDateTimeFields(), true ) ) {
                    $value = date_create( $value );
                }
                $this->$setter_method( $value );
            }
        }
    }

    /**
     * @param string $field
     *
     * @return string
     */
    private function getSetterMethod( string $field ): string {
        return $this->getMethod( 'set', $field );
    }

    /**
     * @param string $field
     *
     * @return string
     */
    private function getPopulateMethod( string $field ): string {
        return $this->getMethod( 'populate', $field );
    }

    /**
     * @param string $prefix
     * @param string $field
     *
     * @return string
     */
    private function getMethod( string $prefix, string $field ): string {
        return $prefix . str_replace( [ '_', '-' ], '', ucwords( $field, '_-' ) );
    }
}
