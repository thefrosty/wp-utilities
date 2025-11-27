<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests;

use Closure;
use PHPUnit\Framework\Attributes\CoversFunction;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;
use TheFrosty\WpUtilities\Exceptions\TerminationException;
use TheFrosty\WpUtilities\Tests\Plugin\Framework\TestCase;
use function TheFrosty\WpUtilities\exitOrThrow;
use function TheFrosty\WpUtilities\getIpAddress;
use function TheFrosty\WpUtilities\isPhpunit;
use function TheFrosty\WpUtilities\wpEnqueueScript;
use function TheFrosty\WpUtilities\wpRegisterScript;
use function var_dump;
use function wp_script_is;

/**
 * Functions Test
 * @package TheFrosty\WpUtilities\Tests
 */
#[CoversFunction('TheFrosty\WpUtilities\exitOrThrow')]
#[CoversFunction('TheFrosty\WpUtilities\getIpAddress')]
#[CoversFunction('TheFrosty\WpUtilities\isPhpunit')]
#[CoversFunction('TheFrosty\WpUtilities\wpEnqueueScript')]
#[CoversFunction('TheFrosty\WpUtilities\wpRegisterScript')]
#[Group('functions')]
class FunctionsTest extends TestCase
{

    public function testExitOrThrow(): void
    {
        $this->expectException(TerminationException::class);
        exitOrThrow();
    }

    public function testGetIpAddressSame(): void
    {
        $this->assertSame('127.0.0.1', getIpAddress());
    }

    public function testGetIpAddressNull(): void
    {
        $request = Request::createFromGlobals();
        $request->server->set('HTTP_CLIENT_IP', '1999.0991.200.89');
        $request->server->set('REMOTE_ADDR', '1999.0991.200.89');
        $this->assertNull(getIpAddress($request));
    }

    public function testIsPhpunit(): void
    {
        $request = Request::createFromGlobals();
        $this->assertTrue(isPhpunit($request));
    }

    public function testIsPhpunitFakeArgV(): void
    {
        $request = Request::createFromGlobals();
        $this->disableArgV($request, function () use ($request): void {
            $this->assertFalse(isPhpunit($request));
        });
    }

    public function testWpRegisterScript(): void
    {
        $this->assertIsBool(wpRegisterScript('script.js', 'script.js'));
        $this->assertIsBool(wpRegisterScript('script.js', 'script.js', args: false));
    }

    public function testWpRegisterScriptPre63(): void
    {
        global $wp_version;
        $temp = $wp_version;
        $wp_version = '6.2';
        $this->assertIsBool(wpRegisterScript('script.js', 'script.js', args: false));
        $wp_version = $temp;
    }

    public function testWpEnqueueScript(): void
    {
        wpEnqueueScript('script.js', 'script.js');
        $this->assertIsBool(wp_script_is('script.js'));
        wpEnqueueScript('script2.js', 'script2.js', args: false);
        $this->assertIsBool(wp_script_is('script.js'));
    }

    public function testWpEnqueueScriptPre63(): void
    {
        global $wp_version;
        $temp = $wp_version;
        $wp_version = '6.2';
        wpEnqueueScript('script2.js', 'script2.js', args: false);
        $this->assertIsBool(wp_script_is('script.js'));
        $wp_version = $temp;
    }

    private function disableArgV(Request $request, Closure $closure): void
    {
        $argv = $request->server->get('argv');
        $request->server->remove('argv');
        $closure();
        // Reset the correct "argv".
        $request->server->set('argv', $argv);
    }
}
