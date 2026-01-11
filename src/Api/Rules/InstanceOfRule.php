<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Api\Rules;

use BlakvGhost\PHPValidator\Contracts\Rule;
use function is_a;

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
        $class = $this->parameters[0];

        return is_a($value, $data[$class], true);
    }

    public function message(): string
    {
        return '';
    }
}
