<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Api\Validator\Rules;

use TheFrosty\WpUtilities\Api\Validator\Contracts\ValidationRule;
use TheFrosty\WpUtilities\Api\Validator\Exceptions\SkipNextRules;

/**
 * Class Nullable
 */
class Nullable implements ValidationRule
{
    /**
     * @param string $ruleName
     * @param string $field
     * @param mixed|null $value
     * @param array|null $constraint
     * @return bool
     * @throws SkipNextRules
     */
    public function apply(string $ruleName, string $field, mixed $value = null, ?array $constraint = null): bool
    {
        if ($value === null) {
            throw new SkipNextRules('');
        }

        return true;
    }
}
