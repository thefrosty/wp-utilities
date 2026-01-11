<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Api\Validator;

use Exception;
use TheFrosty\WpUtilities\Api\Validator\Contracts\ValidationRule;
use TheFrosty\WpUtilities\Utils\AbstractSingleton;

/**
 * Class Validator
 * @package TheFrosty\WpUtilities\Api\Validator
 */
class Validator extends AbstractSingleton
{
    private array $rules;

    /**
     * Validates the given values using the specified rules.
     * @param array $values The values to validate.
     * @param array $rules An array of validation rules to apply to the values.
     * Each rule is an associative array with a rule name as the key and a constraint or constraint name as the value.
     * If the value is a constraint, it is used as a parameter to the rule.
     * @return true If validation passes.
     * @throws \TheFrosty\WpUtilities\Api\Validator\Exceptions\ValidationFailed If validation fails.
     */
    public function validate(array $values, array $rules): true
    {
        foreach ($rules as $inputName => $constraints) {
            foreach ($constraints as $rule => $constraintOrName) {
                $ruleName = is_string($rule) ? $rule : $constraintOrName;
                $constraint = is_string($rule) ? $constraintOrName : null;
                try {
                    $this->findRule($ruleName)->apply(
                        ruleName: $ruleName,
                        field: $inputName,
                        value: $values[$inputName] ?? null,
                        constraint: $constraint
                    );
                } catch (Exception) {
                    continue 2;
                }
            }
        }

        return true;
    }

    /**
     * Register a validation rule with a given name.
     * @param string $name The name to associate with the validation rule.
     * @param string|ValidationRule $rule The validation rule to register. This can be either a string representing the
     *     name of a built-in rule or an instance of a custom ValidationRule class.
     * @return self Returns an instance of the current object, allowing for method chaining.
     */
    public function registerRule(string $name, string|ValidationRule $rule): self
    {
        $this->rules[$name] = $rule;

        return $this;
    }

    /**
     * Finds and returns the validation rule with the specified name.
     * @param string $name The name of the rule to find.
     * @return ValidationRule The found validation rule.
     * @throws Exception If the rule is not found.
     * @throws \TheFrosty\WpUtilities\Api\Validator\Exceptions\ValidationFailed If the rule is not found.
     */
    private function findRule(string $name): ValidationRule
    {
        $this->maybeSetRules();
        if (isset($this->rules[$name])) {
            return is_string($this->rules[$name]) ?
                new $this->rules[$name]() :
                $this->rules[$name];
        }
        throw new Exception('validation rule not found');
    }

    private function maybeSetRules(): void
    {
        $this->rules = (include __DIR__ . '/config/validator.php')['rules'];
    }
}
