<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\Plugin;

use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\Attributes\Group;
use TheFrosty\WpUtilities\Plugin\HooksTrait;
use TheFrosty\WpUtilities\Tests\Plugin\Framework\TestCase;

/**
 * Class HooksTraitMethodsTest
 * @package TheFrosty\WpUtilities\Tests\Plugin
 */
#[CoversTrait(HooksTrait::class)]
#[Group('plugin')]
class HooksTraitMethodsTest extends TestCase
{
    private const string HOOK = 'wp_utilities_test_hooks_trait';

    protected function tearDown(): void
    {
        remove_all_filters(self::HOOK);
        parent::tearDown();
    }

    public function testRemoveFilterAndRemoveAction(): void
    {
        $hooks = $this->hooks();
        $callback = [$hooks, 'suffix'];

        $this->assertTrue($hooks->call('addFilter', self::HOOK, $callback, 5));
        $this->assertSame('value-suffix', apply_filters(self::HOOK, 'value'));
        $this->assertTrue($hooks->call('removeFilter', self::HOOK, $callback, 5));
        $this->assertSame('value', apply_filters(self::HOOK, 'value'));

        $this->assertTrue($hooks->call('addAction', self::HOOK, $callback));
        $this->assertNotFalse(has_action(self::HOOK));
        $this->assertTrue($hooks->call('removeAction', self::HOOK, $callback));
        $this->assertFalse(has_action(self::HOOK));
        $this->assertFalse($hooks->call('removeAction', self::HOOK, $callback));
    }

    public function testDoActionAndApplyFiltersPassArrayArguments(): void
    {
        $hooks = $this->hooks();
        $received = [];
        add_action(self::HOOK, static function (array $first, array $second) use (&$received): void {
            $received = [$first, $second];
        }, 10, 2);

        $hooks->call('doAction', self::HOOK, ['a'], ['b']);
        $this->assertSame([['a'], ['b']], $received);

        remove_all_actions(self::HOOK);
        add_filter(self::HOOK, static fn(string $value, array $extra): string => $value . implode('', $extra), 10, 2);
        $this->assertSame('xyz', $hooks->call('applyFilters', self::HOOK, 'x', ['y', 'z']));
    }

    public function testGetWpFilterIdMatchesCore(): void
    {
        $hooks = $this->hooks();
        $callback = [$hooks, 'suffix'];

        $this->assertSame(
            _wp_filter_build_unique_id(self::HOOK, $callback, 10),
            $hooks->call('getWpFilterId', self::HOOK, $callback, 10)
        );
    }

    public function testOnceHooksRemoveThemselvesAfterRunning(): void
    {
        $hooks = $this->hooks();

        $this->assertTrue($hooks->call('addFilterOnce', self::HOOK, [$hooks, 'suffix']));
        $this->assertNotFalse(has_filter(self::HOOK));
        apply_filters(self::HOOK, 'value');
        $this->assertFalse(has_filter(self::HOOK));

        $this->assertTrue($hooks->call('addActionOnce', self::HOOK, [$hooks, 'suffix']));
        $this->assertNotFalse(has_action(self::HOOK));
        do_action(self::HOOK, 'value');
        $this->assertFalse(has_action(self::HOOK));
    }

    private function hooks(): object
    {
        return new class {
            use HooksTrait;

            public function call(string $method, mixed ...$args): mixed
            {
                return $this->$method(...$args);
            }

            public function suffix(string $value): string
            {
                return $value . '-suffix';
            }
        };
    }
}
