<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\WpAdmin;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\UsesClass;
use ReflectionMethod;
use TheFrosty\WpUtilities\Plugin\Container;
use TheFrosty\WpUtilities\Plugin\Init;
use TheFrosty\WpUtilities\Plugin\Plugin;
use TheFrosty\WpUtilities\Plugin\PluginFactory;
use TheFrosty\WpUtilities\Plugin\TemplateLoader;
use TheFrosty\WpUtilities\Plugin\PluginInterface;
use TheFrosty\WpUtilities\Tests\Plugin\Framework\TestCase;
use TheFrosty\WpUtilities\WpAdmin\DisablePluginUpdateCheck;
use WP_Error;

/**
 * Class DisablePluginUpdateCheckTest
 * @package TheFrosty\WpUtilities\Tests\WpAdmin
 */
#[CoversClass(DisablePluginUpdateCheck::class)]
#[UsesClass(Container::class)]
#[UsesClass(Init::class)]
#[UsesClass(Plugin::class)]
#[UsesClass(PluginFactory::class)]
#[UsesClass(TemplateLoader::class)]
#[Group('wp-admin')]
class DisablePluginUpdateCheckTest extends TestCase
{
    private string $file;

    protected function setUp(): void
    {
        parent::setUp();
        $this->file = sys_get_temp_dir() . '/wp-utilities-plugin.php';
        file_put_contents($this->file, "<?php\n/*\nPlugin Name: Utilities\n*/\n");
        $this->plugin = PluginFactory::create('disable-updates', $this->file);
    }

    protected function tearDown(): void
    {
        remove_all_filters('http_request_args');
        remove_all_filters('pre_http_request');
        remove_all_filters('site_transient_update_plugins');
        parent::tearDown();
    }

    public function testRemovesThePluginFromUpdateRequestsAndTransients(): void
    {
        $checker = new DisablePluginUpdateCheck();
        $checker->setPlugin($this->plugin);
        $checker->addHooks();

        $basename = $this->plugin->getBasename();
        $args = apply_filters('http_request_args', [
            'body' => [
                'plugins' => wp_json_encode(['plugins' => [$basename => ['Name' => 'Utilities']]]),
            ],
        ], 'https://api.wordpress.org/plugins/update-check/1.1/');
        $plugins = json_decode($args['body']['plugins'], true);
        $this->assertArrayNotHasKey($basename, $plugins['plugins']);

        $value = (object)['response' => [$basename => ['new_version' => '9.0.0'], 'other/other.php' => []]];
        $filtered = apply_filters('site_transient_update_plugins', $value);
        $this->assertArrayNotHasKey($basename, $filtered->response);
        $this->assertArrayHasKey('other/other.php', $filtered->response);
    }

    public function testBypassReturnsAnErrorForAMatchingSlug(): void
    {
        $checker = new DisablePluginUpdateCheck();
        $checker->setPlugin($this->plugin);
        $method = new ReflectionMethod(DisablePluginUpdateCheck::class, 'bypassHttpRequest');
        $result = $method->invoke(
            $checker,
            false,
            ['bypass-http-request' => $this->plugin->getSlug()],
            'https://api.wordpress.org/plugins/info/1.2/'
        );

        $this->assertInstanceOf(WP_Error::class, $result);
        $this->assertSame(false, $method->invoke($checker, false, [], 'https://example.com'));
    }

    public function testKeepsTheTransientWhenThePluginHeaderPointsAtGitHub(): void
    {
        file_put_contents(
            $this->file,
            "<?php\n/*\nPlugin Name: Utilities\nGitHub Plugin URI: thefrosty/wp-utilities\n*/\n"
        );
        $plugin = $this->createStub(PluginInterface::class);
        $plugin->method('getBasename')->willReturn('wp-utilities/wp-utilities.php');
        $plugin->method('getSlug')->willReturn('github-updater');
        $plugin->method('getFile')->willReturn($this->file);

        $checker = new DisablePluginUpdateCheck();
        $checker->setPlugin($plugin);
        $value = (object)['response' => ['wp-utilities/wp-utilities.php' => ['new_version' => '9.0.0']]];
        $method = new ReflectionMethod(DisablePluginUpdateCheck::class, 'transientRemovePluginBasename');

        $this->assertArrayHasKey('wp-utilities/wp-utilities.php', $method->invoke($checker, $value)->response);
    }
}
