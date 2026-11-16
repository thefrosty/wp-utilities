<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\PostMeta;

use BlakvGhost\PHPValidator\ValidatorException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\UsesClass;
use TheFrosty\WpUtilities\Api\ValidationRules\IsArray;
use TheFrosty\WpUtilities\Api\ValidationRules\IsCallable;
use TheFrosty\WpUtilities\Api\ValidationRules\IsInstanceOf;
use TheFrosty\WpUtilities\PostMeta\Fields\Text;
use TheFrosty\WpUtilities\PostMeta\FieldsRegistrar;
use TheFrosty\WpUtilities\Tests\Plugin\Framework\TestCase;

/**
 * Class FieldsRegistrarTest
 * @package TheFrosty\WpUtilities\Tests\PostMeta
 */
#[CoversClass(FieldsRegistrar::class)]
#[UsesClass(IsArray::class)]
#[UsesClass(IsCallable::class)]
#[UsesClass(IsInstanceOf::class)]
#[UsesClass(Text::class)]
#[Group('post-meta')]
class FieldsRegistrarTest extends TestCase
{
    protected function tearDown(): void
    {
        FieldsRegistrar::$fields = [];
        parent::tearDown();
    }

    public function testAddGetAndAll(): void
    {
        FieldsRegistrar::add([
            'id' => 'subtitle',
            'label' => 'Subtitle',
            'description' => 'A subtitle',
            'field' => Text::class,
            'single' => true,
            'type' => 'string',
            'types' => ['post'],
        ], 'post');

        $field = FieldsRegistrar::get('subtitle', 'post');
        $this->assertInstanceOf(Text::class, $field);
        $this->assertSame('subtitle', $field->getId());
        $this->assertArrayHasKey('subtitle', FieldsRegistrar::all('post')['post']);
        $this->assertTrue(registered_meta_key_exists('post', 'subtitle', 'post'));
    }

    public function testAddRejectsInvalidFieldArgs(): void
    {
        $this->expectException(ValidatorException::class);
        FieldsRegistrar::add(['label' => 'Missing id'], 'post');
    }
}
