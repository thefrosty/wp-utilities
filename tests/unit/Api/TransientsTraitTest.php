<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\Api;

use TheFrosty\WpUtilities\Api\TransientsTrait;
use TheFrosty\WpUtilities\Tests\Plugin\Framework\TestCase;

/**
 * Trait TransientsTraitTest
 * @package TheFrosty\WpUtilities\Tests\Api
 */
class TransientsTraitTest extends TestCase
{
    private $transientsTrait;

    protected function setUp(): void
    {
        $this->transientsTrait = new class() {
            use TransientsTrait;
        };
        $this->reflection = $this->getReflection($this->transientsTrait);
    }

    public function testGetTransientKey(): void
    {
        $input = 'example_input';
        $keyPrefix = 'prefix_';
        $wp_max_transient_chars = $this->reflection->getProperty('wp_max_transient_chars');
        $getHashedKey = $this->reflection->getMethod('getHashedKey');
        $expectedKey = 'prefix_' . substr(
                $getHashedKey->invoke($this->transientsTrait, $input),
                0,
                $wp_max_transient_chars->getValue($this->transientsTrait) - strlen($keyPrefix)
            );

        $this->assertEquals($expectedKey, $this->transientsTrait->getTransientKey($input, $keyPrefix));
    }

    public function testGetTransient(): void
    {
        $transientName = 'example_transient';
        $expectedValue = 'example_value';

        $this->assertFalse($this->transientsTrait->getTransient($transientName));
        $this->assertNotEquals($expectedValue, $this->transientsTrait->getTransient($transientName));
    }

    public function testSetTransient(): void
    {
        $transientName = 'example_transient';
        $value = 'example_value';
        $expiration = 3600; // 1 hour

        $this->assertTrue($this->transientsTrait->setTransient($transientName, $value, $expiration));
    }

    public function testGetTransientTimeout(): void
    {
        $transientName = 'example_transient';

        $this->assertIsInt($this->transientsTrait->getTransientTimeout($transientName));
        $this->assertEquals(null, $this->transientsTrait->getTransientTimeout('some_random_transient_name'));
    }
}
