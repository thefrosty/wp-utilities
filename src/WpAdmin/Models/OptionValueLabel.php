<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\WpAdmin\Models;

/**
 * Class OptionValueLabel
 * @package TheFrosty\WpUtilities\WpAdmin\Models
 */
class OptionValueLabel
{
    public const string KEY_LABEL = 'label';
    public const string KEY_VALUE = 'value';

    /**
     * OptionValueLabel constructor.
     * @param string $value
     * @param string $label
     */
    public function __construct(
        /**
         * Option value.
         */
        private readonly string $value,
        /**
         * Option label (text).
         */
        private readonly string $label
    ) {
    }

    /**
     * Get the value.
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Get the label
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * Return an array of value/label keys and their values.
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return [
            self::KEY_VALUE => $this->value,
            self::KEY_LABEL => $this->label,
        ];
    }
}
