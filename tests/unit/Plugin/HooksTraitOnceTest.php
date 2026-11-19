<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\Plugin;

use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\Attributes\Group;
use TheFrosty\WpUtilities\Plugin\HooksTrait;
use TheFrosty\WpUtilities\Tests\Plugin\Framework\TestCase;

/**
 * Class HooksTraitOnceTest
 * @package TheFrosty\WpUtilities\Tests\Plugin
 */
#[CoversTrait(HooksTrait::class)]
#[Group('plugin')]
class HooksTraitOnceTest extends TestCase
{
    private const string FILTER = 'wp_utilities_test_filter_once';
    private const string ACTION = 'wp_utilities_test_action_once';

    protected function tearDown(): void
    {
        remove_all_filters(self::FILTER);
        remove_all_actions(self::ACTION);
        parent::tearDown();
    }

    public function testAddFilterOnceAppliesTheCallbackOnlyOnce(): void
    {
        $hooks = $this->hooks();
        $this->assertTrue($hooks->filterOnce(self::FILTER, static fn(string $value): string => strtoupper($value)));

        // Previously the callback returned a Closure instead of the filtered value.
        $this->assertSame('HELLO', apply_filters(self::FILTER, 'hello'));
        $this->assertSame('hello', apply_filters(self::FILTER, 'hello'));
        $this->assertFalse(has_filter(self::FILTER));
    }

    public function testAddFilterOncePassesOnlyTheRequestedNumberOfArguments(): void
    {
        $hooks = $this->hooks();
        $hooks->filterOnce(
            self::FILTER,
            static fn(string $value, string $suffix = ''): string => $value . '|' . func_num_args() . $suffix,
            10,
            2
        );

        $this->assertSame('a|2b', apply_filters(self::FILTER, 'a', 'b', 'ignored'));
    }

    public function testAddActionOnceRunsTheCallbackOnlyOnce(): void
    {
        $hooks = $this->hooks();
        $calls = [];
        $this->assertTrue($hooks->actionOnce(self::ACTION, static function (string $arg) use (&$calls): void {
            $calls[] = $arg;
        }));

        do_action(self::ACTION, 'first');
        do_action(self::ACTION, 'second');

        $this->assertSame(['first'], $calls);
        $this->assertFalse(has_action(self::ACTION));
    }

    private function hooks(): object
    {
        return new class {
            use HooksTrait;

            public function filterOnce(string $hook, callable $method, int $priority = 10, int $args = 1): bool
            {
                return $this->addFilterOnce($hook, $method, $priority, $args);
            }

            public function actionOnce(string $hook, callable $method, int $priority = 10, int $args = 1): bool
            {
                return $this->addActionOnce($hook, $method, $priority, $args);
            }
        };
    }
}
