<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\WpAdmin\Models;

/**
 * Class OptionValueLabel
 * @package TheFrosty\WpUtilities\WpAdmin\Models
 */
class OptionValueLabel
{
    public const KEY_LABEL = 'label';
    public const KEY_VALUE = 'value';

    /**
     * Option value.
     * @var string $value
     */
    private string $value;
    /**
     * Option label (text).
     * @var string $label
     */
    private string $label;

    /**
     * OptionValueLabel constructor.
     * @param string $value
     * @param string $label
     */
    public function __construct(string $value, string $label)
    {
        $this->value = $value;
        $this->label = $label;
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
     * @return array
     */
    public function toArray(): array
    {
        return [
            self::KEY_VALUE => $this->value,
            self::KEY_LABEL => $this->label,
        ];
    }
}
