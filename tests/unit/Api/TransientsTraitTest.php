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

    public function testGetTransientKey()
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

    public function testGetTransient()
    {
        $transientName = 'example_transient';
        $expectedValue = 'example_value';

        // Mocking the get_transient function
        $this->transientsTrait->expects($this->once())
            ->method('getTransient')
            ->with($transientName)
            ->willReturn($expectedValue);

        $this->assertEquals($expectedValue, $this->transientsTrait->getTransient($transientName));
    }

    public function testSetTransient()
    {
        $transientName = 'example_transient';
        $value = 'example_value';
        $expiration = 3600; // 1 hour

        // Mocking the set_transient function
        $this->transientsTrait->expects($this->once())
            ->method('setTransient')
            ->with($transientName, $value, $expiration)
            ->willReturn(true);

        $this->assertTrue($this->transientsTrait->setTransient($transientName, $value, $expiration));
    }

    public function testGetTransientTimeout()
    {
        $transientName = 'example_transient';
        $timeout = 3600; // 1 hour

        global $wpdb;
        $wpdbMock = $this->getMockBuilder('wpdb')
            ->disableOriginalConstructor()
            ->getMock();

        // Mocking the get_col method of $wpdb
        $wpdbMock->expects($this->once())
            ->method('get_col')
            ->willReturn([$timeout]);

        $this->assertEquals($timeout, $this->transientsTrait->getTransientTimeout($transientName));
    }
}
