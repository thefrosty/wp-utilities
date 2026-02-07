<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\Plugin;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use TheFrosty\WpUtilities\Plugin\PluginInterface;

/**
 * Class PluginInterfaceTest
 */
#[CoversNothing]
class PluginInterfaceTest extends TestCase
{
    public function testPluginInterface(): void
    {
        $this->assertTrue(interface_exists(PluginInterface::class));
    }

    public function testInterfaceConstants(): void
    {
        $this->assertContains('DEFAULT_PRIORITY', array_keys((new \ReflectionClass(PluginInterface::class))->getConstants()));
        $this->assertContains('DEFAULT_TAG', array_keys((new \ReflectionClass(PluginInterface::class))->getConstants()));
    }

    public function testInterfaceMethods(): void
    {
        $methods = [
            'getBasename',
            'setBasename',
            'getDirectory',
            'setDirectory',
            'getPath',
            'getTemplateLoader',
            'setTemplateLoader',
            'getInit',
            'setInit',
            'getFile',
            'getFileTime',
            'setFile',
            'getSlug',
            'setSlug',
            'getUrl',
            'setUrl',
            'add',
            'addIfCondition',
            'addIfConditionDeferred',
            'addOnCondition',
            'addOnConditionDeferred',
            'addOnHook',
            'addOnHookDeferred',
        ];

        foreach ($methods as $method) {
            $this->assertTrue(
                method_exists(PluginInterface::class, $method),
                "PluginInterface should have method {$method}"
            );
        }
    }
}
