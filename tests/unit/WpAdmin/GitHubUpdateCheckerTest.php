<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\WpAdmin;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use ReflectionMethod;
use ReflectionProperty;
use TheFrosty\WpUtilities\Plugin\PluginInterface;
use TheFrosty\WpUtilities\Tests\Plugin\Framework\TestCase;
use TheFrosty\WpUtilities\WpAdmin\GitHubUpdateChecker;
use WP_Hook;
use YahnisElsts\PluginUpdateChecker\v6p0\Vcs\GitHubApi;
use YahnisElsts\PluginUpdateChecker\v6p0\Vcs\PluginUpdateChecker;
use function plugin_basename;
use function wp_normalize_path;

/**
 * Class GitHubUpdateCheckerTest
 * @package TheFrosty\WpUtilities\Tests\WpAdmin
 */
#[CoversClass(GitHubUpdateChecker::class)]
#[Group('wp-admin')]
class GitHubUpdateCheckerTest extends TestCase
{
    /** @var list<string> */
    private array $slugs = [];

    protected function tearDown(): void
    {
        foreach ($this->slugs as $slug) {
            $updateChecker = $this->findUpdateChecker($slug);
            if ($updateChecker instanceof PluginUpdateChecker) {
                $this->removeUpdateChecker($updateChecker);
            }
        }

        $this->slugs = [];
        parent::tearDown();
    }

    public function testAddHooksRegistersGitHubCheckerWithoutToken(): void
    {
        $slug = 'wp-utilities';
        $repository = 'thefrosty/wp-utilities';
        $updateChecker = $this->register(new GitHubUpdateChecker($repository), $slug);

        $this->assertSame('https://github.com/' . $repository . '/', $updateChecker->metadataUrl);
        $this->assertSame($slug, $updateChecker->slug);
        $this->assertSame(wp_normalize_path($this->pluginFile($slug)), $updateChecker->pluginAbsolutePath);
        $this->assertSame(plugin_basename($this->pluginFile($slug)), $updateChecker->pluginFile);
        $this->assertGitHubApi($updateChecker, false);
    }

    public function testAddHooksSkipsAuthenticationWhenTokenIsEmpty(): void
    {
        $updateChecker = $this->register(
            new GitHubUpdateChecker('thefrosty/wp-utilities///', ''),
            'wp-utilities-empty-token'
        );

        $this->assertSame('https://github.com/thefrosty/wp-utilities/', $updateChecker->metadataUrl);
        $this->assertGitHubApi($updateChecker, false);
    }

    public function testAddHooksSetsAuthenticationWhenTokenIsPresent(): void
    {
        $token = 'ghp_test_token';
        $updateChecker = $this->register(
            new GitHubUpdateChecker('thefrosty/private-plugin/', $token),
            'private-plugin'
        );

        $this->assertSame('https://github.com/thefrosty/private-plugin/', $updateChecker->metadataUrl);
        $this->assertSame('private-plugin', $updateChecker->slug);
        $api = $this->assertGitHubApi($updateChecker, true);
        $this->assertSame($token, new ReflectionProperty(GitHubApi::class, 'accessToken')->getValue($api));
    }

    public function testBuildUrlStripsTrailingSlashes(): void
    {
        $checker = new GitHubUpdateChecker('thefrosty/wp-utilities///');
        $method = new ReflectionMethod(GitHubUpdateChecker::class, 'buildUrl');

        $this->assertSame('https://github.com/thefrosty/wp-utilities/', $method->invoke($checker));
    }

    private function register(GitHubUpdateChecker $checker, string $slug): PluginUpdateChecker
    {
        $this->slugs[] = $slug;
        $pluginFile = $this->pluginFile($slug);
        $plugin = $this->createStub(PluginInterface::class);
        $plugin->method('getFile')->willReturn($pluginFile);
        $plugin->method('getSlug')->willReturn($slug);
        $checker->setPlugin($plugin);
        $checker->addHooks();

        $updateChecker = $this->findUpdateChecker($slug);
        if (!$updateChecker instanceof PluginUpdateChecker) {
            $this->fail(sprintf('No GitHub update checker was registered for "%s".', $slug));
        }

        return $updateChecker;
    }

    private function findUpdateChecker(string $slug): ?PluginUpdateChecker
    {
        global $wp_filter;

        $hook = $wp_filter['site_transient_update_plugins'] ?? null;
        if (!$hook instanceof WP_Hook) {
            return null;
        }

        foreach ($hook->callbacks as $callbacks) {
            foreach ($callbacks as $callback) {
                $function = $callback['function'] ?? null;
                if (
                    is_array($function) &&
                    ($function[0] ?? null) instanceof PluginUpdateChecker &&
                    $function[0]->slug === $slug
                ) {
                    return $function[0];
                }
            }
        }

        return null;
    }

    private function assertGitHubApi(PluginUpdateChecker $updateChecker, bool $authenticated): GitHubApi
    {
        $api = $updateChecker->getVcsApi();
        $this->assertInstanceOf(GitHubApi::class, $api);
        $this->assertSame($authenticated, $api->isAuthenticationEnabled());
        $this->assertTrue(new ReflectionProperty(GitHubApi::class, 'releaseAssetsEnabled')->getValue($api));

        return $api;
    }

    private function removeUpdateChecker(PluginUpdateChecker $updateChecker): void
    {
        $updateChecker->removeHooks();
        $updateChecker->scheduler->removeUpdaterCron();

        remove_action('uninstall_' . $updateChecker->pluginFile, [$updateChecker, 'removeHooks']);
        remove_action('deactivate_' . $updateChecker->pluginFile, [$updateChecker->scheduler, 'removeUpdaterCron']);
        remove_filter('puc_is_slug_in_use-' . $updateChecker->slug, [$updateChecker, 'getAbsolutePath']);
        remove_action('upgrader_process_complete', [$updateChecker->scheduler, 'removeHooksIfLibraryGone'], 1);
        remove_action('upgrader_process_complete', [$updateChecker->scheduler, 'upgraderProcessComplete'], 11);
        remove_filter('upgrader_pre_download', [$updateChecker->getVcsApi(), 'addDownloadRequestFilters']);

        delete_option($updateChecker->optionName);
        delete_site_option($updateChecker->optionName);
    }

    private function pluginFile(string $slug): string
    {
        return WP_PLUGIN_DIR . '/' . $slug . '/' . $slug . '.php';
    }
}
