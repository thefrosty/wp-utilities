<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\PostTypes\CustomFields\Api;

use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\UsesTrait;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use TheFrosty\WpUtilities\Plugin\HttpFoundationRequestInterface;
use TheFrosty\WpUtilities\Plugin\HttpFoundationRequestTrait;
use TheFrosty\WpUtilities\PostTypes\CustomFields\Api\CurrentPostTrait;
use TheFrosty\WpUtilities\Tests\Plugin\Framework\TestCase;

/**
 * Class CurrentPostTraitTest
 * @package TheFrosty\WpUtilities\Tests\PostTypes\CustomFields\Api
 */
#[CoversTrait(CurrentPostTrait::class)]
#[UsesTrait(HttpFoundationRequestTrait::class)]
#[Group('post-types')]
class CurrentPostTraitTest extends TestCase
{
    public function testThrowsWithoutAnHttpRequest(): void
    {
        $host = new class {
            use CurrentPostTrait;

            public function expose(): ?int
            {
                return $this->getCurrentPostId();
            }
        };

        $this->expectException(RuntimeException::class);
        $host->expose();
    }

    public function testReadsThePostIdFromTheRequest(): void
    {
        $host = new class implements HttpFoundationRequestInterface {
            use CurrentPostTrait;
            use HttpFoundationRequestTrait;

            public function expose(): ?int
            {
                return $this->getCurrentPostId();
            }
        };
        set_current_screen('edit-post');
        $host->setRequest(Request::create('/?post=15', 'POST', ['post_ID' => '9']));

        $this->assertSame(15, $host->expose());

        $body = new class implements HttpFoundationRequestInterface {
            use CurrentPostTrait;
            use HttpFoundationRequestTrait;

            public function expose(): ?int
            {
                return $this->getCurrentPostId();
            }
        };
        $body->setRequest(Request::create('/', 'POST', ['post_ID' => '9']));
        $this->assertSame(9, $body->expose());

        $empty = new class implements HttpFoundationRequestInterface {
            use CurrentPostTrait;
            use HttpFoundationRequestTrait;

            public function expose(): ?int
            {
                return $this->getCurrentPostId();
            }
        };
        unset($GLOBALS['post']);
        $empty->setRequest(Request::create('/'));
        $this->assertNull($empty->expose());
    }
}
