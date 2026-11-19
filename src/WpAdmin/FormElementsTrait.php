<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\WpAdmin;

use InvalidArgumentException;
use TheFrosty\WpUtilities\Plugin\HttpFoundationRequestTrait;
use TheFrosty\WpUtilities\WpAdmin\Models\OptionValueLabel;
use function __;
use function array_walk;
use function esc_attr;
use function esc_html;
use function html_entity_decode;
use function implode;
use function is_array;
use function preg_match_all;
use function printf;
use function sanitize_key;
use function selected;
use function sprintf;
use function strtolower;
use function wp_unslash;
use const ENT_QUOTES;
use const PREG_SET_ORDER;

/**
 * Trait FormElementsTrait
 * @package TheFrosty\WpUtilities\WpAdmin
 */
trait FormElementsTrait
{
    use HttpFoundationRequestTrait;

    /**
     * Build a HTML input element.
     * @param string $name The ID & query parameter.
     * @param string|null $placeholder Input text placeholder.
     */
    protected function inputHtml(string $name, ?string $placeholder = null): void
    {
        printf(
            '<span style="margin-right:6px"><input list="%1$s-list" type="text" id="%1$s" name="%1$s" value="%2$s" 
placeholder="%3$s" title="%4$s"><datalist id="%1$s-list"><option value="%5$s">
<option value="NULL"><option value="EMPTY"></datalist></span>',
            esc_attr(sanitize_key($name)),
            esc_attr(wp_unslash($this->getRequest()->query->get($name))),
            esc_html($placeholder ?? __('Search the value by Meta Key', 'wp-utilities')),
            esc_html(
                __(
                    'Meta Value; search the value by Meta Key. Use "NULL" or "EMPTY" to to search for empty (missing) 
                    value by key. 
---
For advanced search: use `meta_key:"$meta_key" post_title:"$post_title" post_type:"$post_type" to search by the 
post title value (ID).',
                    'wp-utilities'
                )
            ),
            esc_attr('meta_key:"" post_title:"" post_type:""')
        );
    }

    /**
     * Build a HTML select element.
     * @param string $name The ID & query parameter.
     * @param string $default_text The Default text option.
     * @param array|null $options Array of options via [optgroup label => [[OptionValueLabel object]]].
     * @throws InvalidArgumentException
     */
    protected function selectHtml(string $name, string $default_text, ?array $options = []): void
    {
        if (empty($options)) {
            return;
        }

        printf('<select name="%s">', esc_attr(sanitize_key($name)));
        printf('<option value="">%s</option>', esc_html($default_text));
        $current = $this->getRequest()->query->get($name);
        foreach ($options as $optgroup => $option) {
            if (
                !is_array($option) ||
                count(array_filter($option, static fn($entry): bool => !$entry instanceof OptionValueLabel)) > 0
            ) {
                throw new InvalidArgumentException(
                    sprintf('An array of %s values is expected.', OptionValueLabel::class)
                );
            }

            printf('<optgroup label="%s">', esc_attr($optgroup));
            array_walk($option, static function (OptionValueLabel $model) use ($current, $optgroup): void {
                [$label, $extra] = array_pad(explode('|', $model->getLabel()), 2, value: null);
                [$value] = explode('|', $model->getValue());
                printf(
                    '<option value="%1$s" label="%2$s" data-query="%4$s"%3$s%5$s>%2$s</option>',
                    esc_attr($value),
                    esc_html($label),
                    selected($current, $value, false),
                    esc_attr($optgroup),
                    self::buildOptionDataAttributes($extra)
                );
            });
            echo '</optgroup>';
        }

        echo '</select>';
    }

    /**
     * Build the extra option attributes from the label's `|` suffix (e.g. `Label| data-foo="bar"`).
     * Only `data-*` attributes are allowed and every value is escaped, so label content coming from
     * the database (post titles, meta values) can't inject markup or event handlers.
     * @param string|null $extra The raw label suffix.
     * @return string
     */
    private static function buildOptionDataAttributes(?string $extra): string
    {
        if ($extra === null || $extra === '') {
            return '';
        }

        preg_match_all(
            '/(?<![\\w-])(data-[a-z0-9_-]+)\s*=\s*(["\'])(.*?)\2/i',
            html_entity_decode($extra, ENT_QUOTES),
            $matches,
            PREG_SET_ORDER
        );

        $attributes = [];
        foreach ($matches as $match) {
            $attributes[] = sprintf(' %s="%s"', strtolower($match[1]), esc_attr($match[3]));
        }

        return implode('', $attributes);
    }
}
