<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\WpAdmin;

use PHPUnit\Framework\Attributes\CoversClass;
use TheFrosty\WpUtilities\Plugin\PluginFactory;
use TheFrosty\WpUtilities\Plugin\PluginInterface;
use TheFrosty\WpUtilities\Tests\Plugin\Framework\TestCase;
use TheFrosty\WpUtilities\WpAdmin\DisablePluginAutoUpdates;

/**
 * Class DisablePluginAutoUpdatesTest
 * @package TheFrosty\WpUtilities\Tests\WpAdmin
 */
#[CoversClass(DisablePluginAutoUpdates::class)]
class DisablePluginAutoUpdatesTest extends TestCase
{
    private const string OTHER_PLUGIN = 'other/other.php';
    private const string TOGGLE = '<a href="#">Enable auto-updates</a>';

    private string $file;
    private DisablePluginAutoUpdates $auto_updates;

    protected function setUp(): void
    {
        parent::setUp();
        $this->file = sys_get_temp_dir() . '/wp-utilities-plugin.php';
        file_put_contents($this->file, "<?php\n/*\nPlugin Name: Utilities\n*/\n");
        $this->plugin = PluginFactory::create('disable-auto-updates', $this->file);
        $this->auto_updates = new DisablePluginAutoUpdates();
        $this->auto_updates->setPlugin($this->plugin);
        $this->auto_updates->addHooks();
    }

    protected function tearDown(): void
    {
        remove_all_filters('auto_update_plugin');
        remove_all_filters('plugin_auto_update_setting_html');
        remove_all_filters(DisablePluginAutoUpdates::TAG_SETTING_TEXT);
        parent::tearDown();
    }

    public function testNeverAutoUpdatesThePlugin(): void
    {
        foreach ([true, false, null] as $update) {
            $this->assertFalse(apply_filters('auto_update_plugin', $update, $this->item()));
            $this->assertSame($update, apply_filters('auto_update_plugin', $update, $this->item(self::OTHER_PLUGIN)));
        }
    }

    public function testPassesThroughUnexpectedValues(): void
    {
        $this->assertSame(1, apply_filters('auto_update_plugin', 1, (object)[]));
        $this->assertTrue(apply_filters('auto_update_plugin', true, null));
    }

    public function testReplacesTheAutoUpdatesToggleForThePluginOnly(): void
    {
        $html = apply_filters('plugin_auto_update_setting_html', self::TOGGLE, $this->plugin->getBasename(), []);
        $this->assertStringNotContainsString('Enable auto-updates', $html);
        $this->assertStringContainsString('Automatic updates are disabled for this plugin.', $html);

        $other = apply_filters('plugin_auto_update_setting_html', self::TOGGLE, self::OTHER_PLUGIN, []);
        $this->assertSame(self::TOGGLE, $other);
    }

    public function testTheSettingTextIsFilterableAndEscaped(): void
    {
        $received = null;
        add_filter(
            DisablePluginAutoUpdates::TAG_SETTING_TEXT,
            static function (string $text, PluginInterface $plugin) use (&$received): string {
                $received = $plugin;

                return 'Updates are managed with <Composer> & Git.';
            },
            10,
            2
        );

        $html = apply_filters('plugin_auto_update_setting_html', self::TOGGLE, $this->plugin->getBasename(), []);
        $this->assertSame($this->plugin, $received);
        $this->assertStringContainsString('Updates are managed with &lt;Composer&gt; &amp; Git.', $html);
    }

    /**
     * An update offer, as WP_Automatic_Updater passes it to `auto_update_plugin`.
     * @param string|null $plugin
     * @return object
     */
    private function item(?string $plugin = null): object
    {
        return (object)['plugin' => $plugin ?? $this->plugin->getBasename()];
    }
}
