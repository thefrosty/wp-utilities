<?php

declare(strict_types=1);

use TheFrosty\WpUtilities\WpAdmin\Users\Models\UserMetaField;
use TheFrosty\WpUtilities\WpAdmin\Users\Profile;
use TheFrosty\WpUtilities\WpAdmin\Users\Fields\FieldType;
use Symfony\Component\HttpFoundation\Request;

$current_user ??= wp_get_current_user();
/** UserMetaField objects @var UserMetaField[] $fields */
$fields ??= [];
$query ??= Request::createFromGlobals()->query;
$user ??= get_user_by('ID', (int)$query->get('user_id', 0));

if (empty($fields)) {
    return;
}

/**
 * Build the field type input html.
 * @param UserMetaField $field
 * @return string
 */
$buildFieldType = static function (UserMetaField $field) use ($user): string {
    return (new class {
        use FieldType;
    })->{$field->getType()->value}($field, $user);
};

echo '<table class="form-table">';

foreach ($fields as $field) {
    $condition = $field->getCondition();
    if (is_callable($condition) && $condition() === true) {
        continue;
    }
    printf(
        '<tr>
			<th><label for="%1$s">%2$s</label></th>
			<td>
				%3$s
			</td>
		</tr>',
        esc_attr($field->getName()),
        esc_html($field->getLabel()),
        $buildFieldType($field),
    );
}
wp_nonce_field(sprintf(Profile::NONCE_ACTION_S, $user->ID), Profile::ACTION);
echo '</table>';
