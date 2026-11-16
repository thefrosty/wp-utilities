<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\Api;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\UsesClass;
use TheFrosty\WpUtilities\Api\PageTemplates;
use TheFrosty\WpUtilities\Models\PageTemplate;
use TheFrosty\WpUtilities\Tests\Plugin\Framework\TestCase;
use function is_search;

/**
 * Class PageTemplatesTest
 * @package TheFrosty\WpUtilities\Tests\Api
 */
#[CoversClass(PageTemplates::class)]
#[UsesClass(PageTemplate::class)]
#[Group('api')]
class PageTemplatesTest extends TestCase
{
    private string $directory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->directory = sys_get_temp_dir() . '/wp-utilities-page-templates';
        if (!is_dir($this->directory)) {
            mkdir($this->directory);
        }
    }

    public function testEmptyTemplatesLeaveTheDropdownUnchanged(): void
    {
        $templates = new PageTemplates();
        $templates->addHooks();

        $this->assertSame(['default' => 'Default'], apply_filters('theme_page_templates', ['default' => 'Default']));
    }

    public function testTemplatesAreAddedAndIncludedWhenTheFileExists(): void
    {
        $file = $this->directory . '/template-awesome.php';
        file_put_contents($file, '<?php // template');
        $templates = new PageTemplates([
            new PageTemplate([
                'description' => 'Awesome Template',
                'file' => 'template-awesome.php',
                'path' => $file,
            ]),
        ]);
        $templates->addHooks();

        $dropdown = apply_filters('theme_page_templates', ['default' => 'Default']);
        $this->assertSame('Awesome Template', $dropdown['PageTemplates/template-awesome.php']);

        $postId = wp_insert_post([
            'post_title' => 'Template page',
            'post_type' => 'page',
            'post_status' => 'publish',
        ]);
        update_post_meta($postId, '_wp_page_template', 'PageTemplates/template-awesome.php');
        $GLOBALS['post'] = get_post($postId);

        $this->assertSame($file, apply_filters('template_include', 'index.php'));
    }

    public function testTemplateIncludeKeepsTheOriginalTemplate(): void
    {
        $templates = new PageTemplates();
        $templates->addHooks();
        $GLOBALS['post'] = null;

        $this->assertSame('index.php', apply_filters('template_include', 'index.php'));

        $query = $GLOBALS['wp_query'];
        $query->is_search = true;
        $this->assertTrue(is_search());
        $this->assertSame('search.php', apply_filters('template_include', 'search.php'));
        $query->is_search = false;
    }
}
