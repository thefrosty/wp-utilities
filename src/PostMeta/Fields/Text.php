<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\PostMeta\Fields;

use function esc_attr;
use function esc_html;
use function is_scalar;
use function sanitize_text_field;

/**
 * Class Text
 * @package TheFrosty\WpUtilities\PostMeta\Fields
 */
class Text extends AbstractField
{

    public function render(): void
    {
        $value = $this->value();
        ?>
        <label>
            <span class="field-title"><?php echo esc_html($this->getLabel()); ?></span>
            <input type="text" name="<?php echo esc_attr($this->getId()); ?>"
                   value="<?php echo esc_attr(is_scalar($value) ? (string)$value : ''); ?>">
        </label>
        <?php
    }

    /**
     * Sanitize the incoming text value.
     * @param mixed $value
     * @return string
     */
    public function sanitize($value): string
    {
        if (!is_scalar($value)) {
            return '';
        }

        return sanitize_text_field((string)$value);
    }
}
