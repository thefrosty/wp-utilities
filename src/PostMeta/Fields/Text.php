<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\PostMeta\Fields;

use function esc_html;

/**
 * Class Text
 * @package TheFrosty\WpUtilities\PostMeta\Fields
 */
class Text extends AbstractField
{

    public function render(): void
    {
        ?>
        <label>
            <span class="field-title"><?php echo esc_html($this->getLabel()); ?></span>
            <input type="text" name="<?php echo $this->getId(); ?>" value="<?php echo $this->value() ?>">
        </label>
        <?php
    }
}
