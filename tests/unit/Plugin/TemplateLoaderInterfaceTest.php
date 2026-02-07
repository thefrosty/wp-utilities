<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\Plugin;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use TheFrosty\WpUtilities\Plugin\TemplateLoaderInterface;

/**
 * Class TemplateLoaderInterfaceTest
 */
#[CoversNothing]
class TemplateLoaderInterfaceTest extends TestCase
{
    public function testTemplateLoaderInterface(): void
    {
        $this->assertTrue(interface_exists(TemplateLoaderInterface::class));
    }

    public function testInterfaceMethods(): void
    {
        $methods = [
            'setTemplateData',
            'getTemplatePart',
            'loadTemplatePart',
        ];

        foreach ($methods as $method) {
            $this->assertTrue(
                method_exists(TemplateLoaderInterface::class, $method),
                "TemplateLoaderInterface should have method {$method}"
            );
        }
    }
}
