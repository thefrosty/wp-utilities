<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Api\Rules;

use BlakvGhost\PHPValidator\Contracts\Rule;
use BlakvGhost\PHPValidator\Lang\LangManager;
use function is_array;

/**
 * Class ArrayRule
 * @package TheFrosty\WpUtilities\Api\Rules
 */
class ArrayRule implements Rule
{

    protected string $field;

    public function __construct(protected array $parameters = [])
    {
    }

    public function passes(string $field, mixed $value, array $data): bool
    {
        $this->field = $field;

        return is_array($value);
    }

    public function message(): string
    {
        return LangManager::getTranslation('validation.array', [
            'attribute' => $this->field,
        ]);
    }
}
