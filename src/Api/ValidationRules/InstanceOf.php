<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Api\ValidationRules;

use BlakvGhost\PHPValidator\Contracts\Rule;
use function is_a;
use function is_subclass_of;
use function sprintf;

/**
 * Class InstanceOfRule
 * @package TheFrosty\WpUtilities\Api\Rules
 */
class InstanceOfRule implements Rule
{

    protected string $field;

    public function __construct(protected array $parameters)
    {
    }

    public function passes(string $field, string $value, array $data): bool
    {
        $this->field = $field;
        $class = $this->parameters[0] ?? null;

        return isset($class) && (is_a($value, $class, true) || is_subclass_of($value, $class));
    }

    public function message(): string
    {
        return sprintf('The %s field must be an instance of %s.', $this->field, $this->parameters[0] ?? '');
    }
}
