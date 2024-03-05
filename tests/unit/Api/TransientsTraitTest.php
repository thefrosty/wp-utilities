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
        $wp_max_transient_chars->setAccessible(true);
        $expectedKey = 'prefix_' . substr(
                md5($input),
                0,
                $wp_max_transient_chars->getValue($this->transientsTrait) - strlen($keyPrefix)
            );

        $this->assertEquals($expectedKey, $this->transientsTrait->getTransientKey($input, $keyPrefix));
    }

    public function testGetTransient(): void
    {
        $transientName = 'example_transient';
        $expectedValue = 'example_value';

        $this->assertEquals($expectedValue, $this->transientsTrait->getTransient($transientName));
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

        $this->assertEquals(null, $this->transientsTrait->getTransientTimeout($transientName));
    }
}
