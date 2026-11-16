<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\WpAdmin;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\Attributes\UsesTrait;
use ReflectionProperty;
use Symfony\Component\HttpFoundation\Request;
use TheFrosty\WpUtilities\Plugin\HttpFoundationRequestTrait;
use TheFrosty\WpUtilities\Tests\Plugin\Framework\TestCase;
use TheFrosty\WpUtilities\WpAdmin\FormElementsTrait;
use TheFrosty\WpUtilities\WpAdmin\Models\OptionValueLabel;

/**
 * Class FormElementsTraitTest
 * @package TheFrosty\WpUtilities\Tests\WpAdmin
 */
#[CoversTrait(FormElementsTrait::class)]
#[UsesTrait(HttpFoundationRequestTrait::class)]
#[UsesClass(OptionValueLabel::class)]
#[Group('wp-admin')]
class FormElementsTraitTest extends TestCase
{
    public function testInputAndSelectHtml(): void
    {
        $form = $this->form(Request::create('/?meta_key=color'));

        ob_start();
        $form->input('meta_key', 'Search');
        $input = ob_get_clean();
        $this->assertStringContainsString('name="meta_key"', $input);
        $this->assertStringContainsString('value="color"', $input);

        ob_start();
        $form->select('meta_key', 'Choose', [
            'Colors' => [new OptionValueLabel('red', 'Red| data-extra="1"')],
        ]);
        $select = ob_get_clean();
        $this->assertStringContainsString('<optgroup label="Colors">', $select);
        $this->assertStringContainsString('value="red"', $select);
        $this->assertStringContainsString('Red', $select);
    }

    public function testSelectHtmlRejectsInvalidOptionsAndSkipsAnEmptyList(): void
    {
        $form = $this->form(Request::create('/'));
        ob_start();
        $form->select('meta_key', 'Choose', []);
        $this->assertSame('', ob_get_clean());

        ob_start();
        try {
            $form->select('meta_key', 'Choose', ['Bad' => ['nope']]);
            ob_end_clean();
            $this->fail('Invalid options should throw.');
        } catch (InvalidArgumentException) {
            ob_end_clean();
        }
    }

    private function form(Request $request): object
    {
        $form = new class {
            use FormElementsTrait;

            public function input(string $name, ?string $placeholder = null): void
            {
                $this->inputHtml($name, $placeholder);
            }

            public function select(string $name, string $default, ?array $options = []): void
            {
                $this->selectHtml($name, $default, $options);
            }
        };
        $property = new ReflectionProperty($form, 'request');
        $property->setValue(null, null);
        $form->setRequest($request);

        return $form;
    }
}
