<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Api\Validator\Rules;

use TheFrosty\WpUtilities\Api\Validator\Contracts\ValidationRule;
use TheFrosty\WpUtilities\Api\Validator\Exceptions\ValidationFailed;

/**
 * Class Required
 */
class Required implements ValidationRule
{
    /**
     * @param string $ruleName
     * @param string $field
     * @param mixed|null $value
     * @param array|null $constraint
     * @return bool
     * @throws ValidationFailed
     */
    public function apply(string $ruleName, string $field, mixed $value = null, ?array $constraint = null): bool
    {
        if ($value !== null) {
            return true;
        }

        throw new ValidationFailed("The field `$field` is required.");
    }
}
