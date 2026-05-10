<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\Integration\CarbonFields;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\UsesClass;
use TheFrosty\WpUtilities\Integration\CarbonFields\CarbonFields;
use TheFrosty\WpUtilities\Integration\CarbonFields\FieldsInterface;
use TheFrosty\WpUtilities\Integration\CarbonFields\TypeInterface;
use TheFrosty\WpUtilities\Plugin\AbstractContainerProvider;
use TheFrosty\WpUtilities\Tests\Plugin\Framework\TestCase;

/**
 * Class CarbonFieldsTest
 * @package TheFrosty\WpUtilities\Tests\Integration\CarbonFields
 */
#[CoversClass(CarbonFields::class)]
#[UsesClass(FieldsInterface::class)]
#[UsesClass(TypeInterface::class)]
#[UsesClass(AbstractContainerProvider::class)]
#[Group('integration')]
class CarbonFieldsTest extends TestCase
{
    protected $carbonFields;

    /**
     * Set up.
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->carbonFields = $this->getMockProviderForAbstractClass(CarbonFields::class);
    }

    /**
     * Tear down.
     */
    public function tearDown(): void
    {
        unset($this->carbonFields);
        parent::tearDown();
    }

    /**
     * Test that CarbonFields implements FieldsInterface.
     */
    public function testImplementsFieldsInterface(): void
    {
        $this->assertInstanceOf(FieldsInterface::class, $this->carbonFields);
    }

    /**
     * Test that CarbonFields implements TypeInterface.
     */
    public function testImplementsTypeInterface(): void
    {
        $this->assertInstanceOf(TypeInterface::class, $this->carbonFields);
    }

    /**
     * Test addHooks method.
     */
    public function testAddHooks(): void
    {
        // We can't mock addAction since it's in the parent class
        // Just verify that the method executes without error
        $this->expectNotToPerformAssertions();
        $this->carbonFields->addHooks();
    }

    /**
     * Test postMetaContainer method.
     */
    public function testPostMetaContainer(): void
    {
        // Mock the Container::make method to return a mock
        $this->expectNotToPerformAssertions();
        $this->carbonFields->postMetaContainer('Test Label');
    }

    /**
     * Test termMetaContainer method.
     */
    public function testTermMetaContainer(): void
    {
        // Mock the Container::make method to return a mock
        $this->expectNotToPerformAssertions();
        $this->carbonFields->termMetaContainer('Test Label');
    }

    /**
     * Test loaded method.
     */
    public function testLoaded(): void
    {
        // We can't mock addAction since it's in the parent class
        // Just verify that the method executes without error
        $this->expectNotToPerformAssertions();
        $this->carbonFields->loaded();
    }

    /**
     * Test reEnqueueScripts method.
     */
    public function testReEnqueueScripts(): void
    {
        $this->expectOutputString('');
        // Just verify that the method executes without error
        $this->carbonFields->reEnqueueScripts();
    }

    /**
     * Test getName method.
     */
    public function testGetName(): void
    {
        // Just verify the method exists and doesn't throw an error
        $this->expectNotToPerformAssertions();
        $this->carbonFields->getName('test_name');
    }
}
