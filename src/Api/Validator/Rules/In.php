<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Api\Validator\Rules;

use TheFrosty\WpUtilities\Api\Validator\Contracts\ValidationRule;
use TheFrosty\WpUtilities\Api\Validator\Exceptions\ValidationFailed;

/**
 * Class In
 */
class In implements ValidationRule
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
        if (in_array($value, $constraint, true)) {
            return true;
        }

        throw new ValidationFailed(
            "The value for field `$field` is not in the required list: " . implode(',', $constraint)
        );
    }
}
