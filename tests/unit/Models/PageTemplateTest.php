<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\Models;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use TheFrosty\WpUtilities\Models\PageTemplate;
use TheFrosty\WpUtilities\Tests\Plugin\Framework\TestCase;

/**
 * Class PageTemplateTest
 * @package TheFrosty\WpUtilities\Tests\Models
 */
#[CoversClass(PageTemplate::class)]
#[Group('models')]
class PageTemplateTest extends TestCase
{
    public function testFieldsRoundTripThroughToArray(): void
    {
        $template = new PageTemplate([
            'description' => 'Awesome Template',
            'file' => 'template-awesome.php',
            'path' => '/themes/template-awesome.php',
        ]);

        $this->assertSame('Awesome Template', $template->getDescription());
        $this->assertSame('template-awesome.php', $template->getFile());
        $this->assertSame('/themes/template-awesome.php', $template->getPath());
        $this->assertSame(
            [
                PageTemplate::FIELD_DESC => 'Awesome Template',
                PageTemplate::FIELD_FILE => 'template-awesome.php',
                PageTemplate::FIELD_PATH => '/themes/template-awesome.php',
            ],
            $template->toArray()
        );
    }
}
