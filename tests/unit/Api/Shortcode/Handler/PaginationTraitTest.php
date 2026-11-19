<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\Api\Shortcode\Handler;

use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\Attributes\Group;
use ReflectionMethod;
use TheFrosty\WpUtilities\Api\Shortcode\Handler\PaginationTrait;
use TheFrosty\WpUtilities\Tests\Plugin\Framework\TestCase;
use WP_Query;

/**
 * Class PaginationTraitTest
 * @package TheFrosty\WpUtilities\Tests\Api\Shortcode\Handler
 */
#[CoversTrait(PaginationTrait::class)]
#[Group('api')]
class PaginationTraitTest extends TestCase
{
    public function testPaginationRendersFoundationStyleMarkup(): void
    {
        $query = new WP_Query();
        $query->max_num_pages = 3;

        $html = $this->paginate($query);

        $this->assertStringStartsWith('<div class="pagination-centered">', $html);
        $this->assertStringContainsString("<ul class='pagination'>", $html);
        $this->assertStringContainsString('&raquo;', $html);
        $this->assertStringNotContainsString('page-numbers', $html);
    }

    public function testPaginationIsEmptyForASinglePage(): void
    {
        $query = new WP_Query();
        $query->max_num_pages = 1;

        $this->assertSame('', $this->paginate($query));
    }

    private function paginate(WP_Query $query): string
    {
        $host = new class {
            use PaginationTrait;
        };

        return (new ReflectionMethod($host, 'pagination'))->invoke($host, $query);
    }
}
