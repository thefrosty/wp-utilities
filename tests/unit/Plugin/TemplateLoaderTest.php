<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\Plugin;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\UsesClass;
use RuntimeException;
use TheFrosty\WpUtilities\Plugin\Container;
use TheFrosty\WpUtilities\Plugin\Init;
use TheFrosty\WpUtilities\Plugin\Plugin;
use TheFrosty\WpUtilities\Plugin\PluginFactory;
use TheFrosty\WpUtilities\Plugin\TemplateLoader;
use TheFrosty\WpUtilities\Tests\Plugin\Framework\TestCase;

/**
 * Class TemplateLoaderTest
 * @package TheFrosty\WpUtilities\Tests\Plugin
 */
#[CoversClass(TemplateLoader::class)]
#[UsesClass(Container::class)]
#[UsesClass(Init::class)]
#[UsesClass(Plugin::class)]
#[UsesClass(PluginFactory::class)]
#[Group('plugin')]
class TemplateLoaderTest extends TestCase
{
    private string $directory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->directory = sys_get_temp_dir() . '/wp-utilities-template-loader';
        if (!is_dir($this->directory . '/views')) {
            mkdir($this->directory . '/views', 0777, true);
        }
        file_put_contents($this->directory . '/plugin.php', '<?php // plugin');
        file_put_contents($this->directory . '/views/card.php', '<?php echo "card";');
        file_put_contents($this->directory . '/views/card-alt.php', '<?php echo "alt";');
        $this->plugin = PluginFactory::create('template-loader-test', $this->directory . '/plugin.php');
    }

    public function testLoadsTheMostSpecificExistingTemplate(): void
    {
        $loader = $this->plugin->getTemplateLoader();
        $loader->setTemplateData(['title' => 'Card'], 'card_data');

        $this->assertSame(['title' => 'Card'], TemplateLoader::getData('card_data'));
        $this->assertSame($this->directory . '/views/card-alt.php', $loader->getTemplatePart('card', 'alt'));
        $this->assertSame($this->directory . '/views/card-alt.php', $loader->getTemplatePart('card', 'alt'));
        $this->assertSame($this->directory . '/views/card.php', $loader->getTemplatePart('card'));

        ob_start();
        $loader->loadTemplatePart('card');
        $this->assertSame('card', ob_get_clean());
    }

    public function testMissingTemplateThrows(): void
    {
        $this->expectException(RuntimeException::class);
        $this->plugin->getTemplateLoader()->getTemplatePart('missing');
    }

    public function testTemplateNamesCannotTraverseOutOfTheTemplatePaths(): void
    {
        // `plugin.php` exists one level above `views/`.
        $loader = $this->plugin->getTemplateLoader();

        $this->expectException(RuntimeException::class);
        $loader->getTemplatePart('../plugin');
    }
    public function testDestructorRemovesTheTemplateDataQueryVars(): void
    {
        $loader = new TemplateLoader($this->plugin);
        $loader->setTemplateData(['title' => 'Temporary'], 'temporary_data');
        $this->assertSame(['title' => 'Temporary'], TemplateLoader::getData('temporary_data'));

        unset($loader);

        $this->assertSame([], TemplateLoader::getData('temporary_data'));
    }

}
