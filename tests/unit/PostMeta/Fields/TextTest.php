<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\PostMeta\Fields;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\UsesClass;
use ReflectionProperty;
use TheFrosty\WpUtilities\PostMeta\Fields\AbstractField;
use TheFrosty\WpUtilities\PostMeta\Fields\Text;
use TheFrosty\WpUtilities\PostMeta\PostMetaManager;
use TheFrosty\WpUtilities\Tests\Plugin\Framework\TestCase;

/**
 * Class TextTest
 * @package TheFrosty\WpUtilities\Tests\PostMeta\Fields
 */
#[CoversClass(Text::class)]
#[UsesClass(AbstractField::class)]
#[UsesClass(PostMetaManager::class)]
#[Group('post-meta')]
class TextTest extends TestCase
{
    public function testRenderEscapesTheIdAndStoredValue(): void
    {
        $post_id = wp_insert_post(['post_title' => 'Text field', 'post_status' => 'publish']);
        update_post_meta($post_id, 'subtitle', '"><script>alert(1)</script>');

        $field = new Text(['id' => 'subtitle', 'label' => '<i>Subtitle</i>']);
        $manager = new PostMetaManager('details', 'Details', ['subtitle' => $field]);
        (new ReflectionProperty(PostMetaManager::class, 'post'))->setValue($manager, get_post($post_id));
        $field->manager = $manager;
        // A field registered with a hostile ID must not break out of the name attribute either.
        $hostile = new Text(['id' => 'x"><b>', 'label' => 'Label']);
        $hostile->manager = $manager;

        ob_start();
        $field->render();
        $hostile->render();
        $html = ob_get_clean();

        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringNotContainsString('<b>', $html);
        $this->assertStringNotContainsString('<i>', $html);
        $this->assertStringContainsString('value="&quot;&gt;&lt;script&gt;alert(1)&lt;/script&gt;"', $html);
    }

    public function testSanitizeStripsTagsAndRejectsNonScalarValues(): void
    {
        $field = new Text(['id' => 'subtitle', 'label' => 'Subtitle']);

        $this->assertSame('Hello', $field->sanitize('<b>Hello</b>'));
        $this->assertSame('42', $field->sanitize(42));
        $this->assertSame('', $field->sanitize(['<b>array</b>']));
        $this->assertSame('', $field->sanitize(null));
    }
}
