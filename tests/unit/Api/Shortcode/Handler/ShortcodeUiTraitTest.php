<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\Api\Shortcode\Handler;

use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\Attributes\Group;
use RuntimeException;
use TheFrosty\WpUtilities\Api\Shortcode\Handler\ShortcodeUiTrait;
use TheFrosty\WpUtilities\Tests\Plugin\Framework\TestCase;
use function function_exists;

/**
 * Class ShortcodeUiTraitTest
 * @package TheFrosty\WpUtilities\Tests\Api\Shortcode\Handler
 */
#[CoversTrait(ShortcodeUiTrait::class)]
#[Group('api')]
class ShortcodeUiTraitTest extends TestCase
{
    public function testAddActionThrowsWhenShortcakeIsMissing(): void
    {
        if (function_exists('shortcode_ui_register_for_shortcode')) {
            $this->markTestSkipped('Shortcake is already loaded.');
        }

        $host = $this->host();
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Shortcake plugin needs to be activated');
        $host->exposeAddAction();
    }

    public function testShortcodeRegistrationIsSkippedWhenShortcakeIsMissing(): void
    {
        if (function_exists('shortcode_ui_register_for_shortcode')) {
            $this->markTestSkipped('Shortcake is already loaded.');
        }

        $this->host()->exposeRegister('demo', ['label' => 'Demo']);
        $this->assertFalse(function_exists('shortcode_ui_register_for_shortcode'));
    }

    public function testRegistersShortcodeUiWhenShortcakeIsAvailable(): void
    {
        if (!function_exists('shortcode_ui_register_for_shortcode')) {
            eval(<<<'PHP'
                function shortcode_ui_register_for_shortcode($slug, $args): void
                {
                    $GLOBALS['wp_utilities_shortcode_ui'] = [$slug, $args];
                }
            PHP);
        }

        $host = $this->host();
        $host->exposeAddAction();
        $this->assertNotFalse(has_action('register_shortcode_ui', [$host, 'registerShortcodeUI']));

        $host->exposeRegister('demo', ['label' => 'Demo']);
        $this->assertSame(['demo', ['label' => 'Demo']], $GLOBALS['wp_utilities_shortcode_ui']);
    }

    private function host(): object
    {
        return new class {
            use ShortcodeUiTrait;

            public bool $registered = false;

            public function registerShortcodeUI(): void
            {
                $this->registered = true;
            }

            public function pluginsLoaded(): void
            {
            }

            public function exposeAddAction(): void
            {
                $this->addActionRegisterShortcodeUi();
            }

            public function exposeRegister(string $slug, array $args): void
            {
                $this->shortcodeUiRegisterShortcode($slug, $args);
            }
        };
    }
}
