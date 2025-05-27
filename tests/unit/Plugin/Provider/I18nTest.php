<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\Plugin\Provider;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use ReflectionObject;
use TheFrosty\WpUtilities\Plugin\AbstractPlugin;
use TheFrosty\WpUtilities\Plugin\ContainerAwareTrait;
use TheFrosty\WpUtilities\Plugin\PluginFactory;
use TheFrosty\WpUtilities\Plugin\Provider\I18n;
use TheFrosty\WpUtilities\Plugin\TemplateLoader;
use TheFrosty\WpUtilities\Tests\Plugin\Framework\TestCase;
use WP_Textdomain_Registry;
use function do_action;

/**
 * Class I18nTest
 * @package TheFrosty\WpUtilities\Tests\Plugin\Provider
 */
#[CoversClass(I18n::class)]
#[UsesClass(AbstractPlugin::class)]
#[UsesClass(ContainerAwareTrait::class)]
#[UsesClass(PluginFactory::class)]
#[UsesClass(TemplateLoader::class)]
class I18nTest extends TestCase
{

    protected I18n $i18n;

    public function setUp(): void
    {
        parent::setUp();
        $this->plugin = PluginFactory::create('i18n');
        $this->i18n = new I18n();
        $this->i18n->setPlugin($this->plugin);
        $this->reflection = new ReflectionObject($this->i18n);
    }

    public function tearDown(): void
    {
        unset($this->plugin);
        parent::tearDown();
    }

    public function testAddHooks(): void
    {
        $this->i18n->addHooks();
        $this->assertTrue($this->getRegistry()->has($this->i18n->getPlugin()->getSlug()));
    }

    public function testLoadTextDomain(): void
    {
        $loadTextDomain = $this->reflection->getMethod('loadTextDomain');
        $loadTextDomain->invoke($this->i18n);
        $this->assertTrue($this->getRegistry()->has($this->i18n->getPlugin()->getSlug()));
    }

    private function getRegistry(): WP_Textdomain_Registry
    {
        global $wp_textdomain_registry;
        return $wp_textdomain_registry;
    }
}
