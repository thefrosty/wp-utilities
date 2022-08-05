<?php declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\Plugin;

use TheFrosty\WpUtilities\Plugin\ContainerAwareTrait;
use TheFrosty\WpUtilities\Plugin\Plugin;
use TheFrosty\WpUtilities\Plugin\PluginInterface;
use TheFrosty\WpUtilities\Tests\Plugin\Framework\TestCase;

/**
 * Class PluginTest
 *
 * @package TheFrosty\WpUtilities\Test\Plugin
 */
class PluginTest extends TestCase
{

    /**
     * Set up.
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->plugin = new Plugin();
        $this->reflection = new \ReflectionObject($this->plugin);
    }

    /**
     * Tear down.
     */
    public function tearDown(): void
    {
        unset($this->plugin);
        parent::tearDown();
    }

    /**
     * Test PluginInterface.
     */
    public function testPluginInterface(): void
    {
        $this->assertInstanceOf(PluginInterface::class, $this->plugin);
    }

    /**
     * Test Plugin().
     */
    public function testPlugin(): void
    {
        $traits = \class_uses($this->plugin);
        $this->assertIsArray($traits);
        $this->assertCount(1, $traits);
        $this->assertArrayHasKey(ContainerAwareTrait::class, $traits);
        $constants = $this->getClassConstants($this->reflection);
        $this->assertCount(1, $constants);
        $this->assertArrayHasKey('TAG', $constants);
    }
}
