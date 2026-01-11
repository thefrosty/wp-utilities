<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\Api\Validator;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use TheFrosty\WpUtilities\Api\Validator\Contracts\ValidationRule;
use TheFrosty\WpUtilities\Api\Validator\Exceptions\ValidationFailed;
use TheFrosty\WpUtilities\Api\Validator\Rules\In;
use TheFrosty\WpUtilities\Api\Validator\Rules\Nullable;
use TheFrosty\WpUtilities\Api\Validator\Rules\Required;
use TheFrosty\WpUtilities\Api\Validator\Validator;
use TheFrosty\WpUtilities\Tests\Plugin\Framework\TestCase;

#[CoversClass(Validator::class)]
#[CoversClass(In::class)]
#[CoversClass(Nullable::class)]
#[CoversClass(Required::class)]
#[Group('api')]
#[Group('validator')]
class ValidatorTest extends TestCase
{
    private Validator $validator;

    protected function setUp(): void
    {
        $this->validator = Validator::getInstance();
        $this->reflection = $this->getReflection($this->validator);
    }

    public function testValidateRequiredField(): void
    {
        $rules = [
            'field_1' => ['required'],
        ];

        $result = $this->validator->validate(values: ['field_1' => 'value'], rules: $rules);

        $this->assertTrue($result);

        $this->expectException(ValidationFailed::class);

        $this->validator->validate(values: ['another_field' => 'value'], rules: $rules);
    }

    public function testCanValidateMoreThanOneRule(): void
    {
        $rules = [
            'field_1' => ['required', 'in' => ['value', 'another value']],
        ];

        try {
            $this->validator->validate(values: ['field_1' => null], rules: $rules);
        } catch (ValidationFailed $e) {
            $this->assertEquals('The field `field_1` is required.', $e->getMessage());
        }

        try {
            $this->validator->validate(values: ['field_1' => 'bad value'], rules: $rules);
        } catch (ValidationFailed $e) {
            $this->assertEquals(
                'The value for field `field_1` is not in the required list: value,another value',
                $e->getMessage()
            );
        }

        $result = $this->validator->validate(values: ['field_1' => 'value'], rules: $rules);

        $this->assertTrue($result);
    }

    public function testCanValidateNullValues(): void
    {
        $rules = [
            'field_1' => ['nullable', 'in' => ['value', 'another value']],
        ];

        $result = $this->validator->validate(values: ['field_1' => null], rules: $rules);
        $this->assertTrue($result);

        $result = $this->validator->validate(values: ['field_1' => 'value'], rules: $rules);
        $this->assertTrue($result);

        try {
            $this->validator->validate(values: ['field_1' => 'bad value'], rules: $rules);
        } catch (ValidationFailed $e) {
            $this->assertEquals(
                'The value for field `field_1` is not in the required list: value,another value',
                $e->getMessage()
            );
        }
    }


    public function testCanAddNewRuleDynamically(): void
    {
        $rules = ['field_1' => ['is_pi']];

        $this->validator->registerRule(
            'is_pi',
            new class implements ValidationRule {
                public function apply(
                    string $ruleName,
                    string $field,
                    mixed $value = null,
                    mixed $constraint = null
                ): bool {
                    if ($value === 3.14) {
                        return true;
                    }
                    throw new ValidationFailed(
                        "The value for field `$field` is not Pi (3.14), you provided: $value."
                    );
                }
            }
        );

        $this->assertTrue($this->validator->validate(values: ['field_1' => 3.14], rules: $rules));

        $this->expectException(ValidationFailed::class);
        $this->validator->validate(values: ['field_1' => 3.15], rules: $rules);

        try {
            $this->validator->validate(values: ['field_1' => 3.15], rules: $rules);
        } catch (ValidationFailed $e) {
            $this->assertEquals(
                'The value for field `field_1` is not Pi (3.14), you provided: 3.15.',
                $e->getMessage()
            );
        }
    }
}
