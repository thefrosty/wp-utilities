<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\Api\Shortcode;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\UsesTrait;
use TheFrosty\WpUtilities\Api\Shortcode\AbstractRegistrar;
use TheFrosty\WpUtilities\Api\Shortcode\AbstractShortcode;
use TheFrosty\WpUtilities\Api\Shortcode\Handler\HandlerInterface;
use TheFrosty\WpUtilities\Api\Shortcode\Shortcode;
use TheFrosty\WpUtilities\Api\Shortcode\ShortcodeRegistrar;
use TheFrosty\WpUtilities\Plugin\HooksTrait;
use TheFrosty\WpUtilities\Tests\Plugin\Framework\TestCase;

/**
 * Class ShortcodeTest
 * @package TheFrosty\WpUtilities\Tests\Api\Shortcode
 */
#[CoversClass(AbstractRegistrar::class)]
#[CoversClass(AbstractShortcode::class)]
#[CoversClass(Shortcode::class)]
#[CoversClass(ShortcodeRegistrar::class)]
#[UsesTrait(HooksTrait::class)]
#[Group('api')]
class ShortcodeTest extends TestCase
{
    private const string TAG = 'wpu_test_shortcode';

    protected function tearDown(): void
    {
        remove_shortcode(self::TAG);
        parent::tearDown();
    }

    public function testShortcodeSetsTheHandlerTagAndCallsPluginsLoaded(): void
    {
        $handler = $this->handler();
        $shortcode = new Shortcode(self::TAG, $handler);

        $this->assertSame(self::TAG, $shortcode->getTag());
        $this->assertSame($handler, $shortcode->getHandler());
        $this->assertSame(self::TAG, $handler->tag);
        $this->assertTrue($handler->loaded);
    }

    public function testRegistrarAddsTheShortcodeOnInit(): void
    {
        $registrar = new class (new Shortcode(self::TAG, $this->handler())) extends ShortcodeRegistrar {
            public function removeHooks(): bool
            {
                return $this->removeAction('init', [$this, 'addShortcode']);
            }
        };
        $registrar->addHooks();
        $this->assertFalse(shortcode_exists(self::TAG));
        $this->assertNotFalse(has_action('init'));

        // `init` has already fired in the test bootstrap; call the hooked method directly.
        $registrar->addShortcode();

        $this->assertTrue(shortcode_exists(self::TAG));
        // Don't leave the hook behind for other tests that fire `init`.
        $this->assertTrue($registrar->removeHooks());
        $this->assertSame('[' . self::TAG . ':hello]', do_shortcode('[' . self::TAG . ']hello[/' . self::TAG . ']'));
    }

    private function handler(): HandlerInterface
    {
        return new class implements HandlerInterface {
            public string $tag = '';
            public bool $loaded = false;

            public function setTag(string $tag): void
            {
                $this->tag = $tag;
            }

            public function getDefaults(): array
            {
                return [];
            }

            public function handler(array|string $atts, ?string $content, string $tag): string
            {
                return sprintf('[%s:%s]', $tag, $content);
            }

            public function pluginsLoaded(): void
            {
                $this->loaded = true;
            }
        };
    }
}
