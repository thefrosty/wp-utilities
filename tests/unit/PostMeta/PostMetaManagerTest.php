<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\PostMeta;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\Attributes\UsesTrait;
use ReflectionProperty;
use TheFrosty\WpUtilities\Plugin\HttpFoundationRequestTrait;
use TheFrosty\WpUtilities\PostMeta\AbstractManager;
use TheFrosty\WpUtilities\PostMeta\Fields\Text;
use TheFrosty\WpUtilities\PostMeta\FieldsRegistrar;
use TheFrosty\WpUtilities\PostMeta\PostMetaManager;
use TheFrosty\WpUtilities\Tests\Plugin\Framework\TestCase;
use WP_Post;

/**
 * Class PostMetaManagerTest
 * @package TheFrosty\WpUtilities\Tests\PostMeta
 */
#[CoversClass(PostMetaManager::class)]
#[UsesClass(FieldsRegistrar::class)]
#[UsesClass(Text::class)]
#[UsesTrait(HttpFoundationRequestTrait::class)]
#[Group('post-meta')]
class PostMetaManagerTest extends TestCase
{
    private PostMetaManager $manager;

    private Text $field;

    private int $postId;

    protected function setUp(): void
    {
        parent::setUp();
        if (!function_exists('add_meta_box')) {
            require_once ABSPATH . 'wp-admin/includes/template.php';
        }
        $this->field = new Text(['id' => 'subtitle', 'label' => 'Subtitle']);
        $this->manager = new PostMetaManager('details', 'Details', ['subtitle' => $this->field]);
        $this->manager->setRequest();
        $this->field->manager = $this->manager;
        $this->postId = wp_insert_post([
            'post_title' => 'Meta post',
            'post_status' => 'publish',
        ]);
        wp_set_current_user(1);
    }

    protected function tearDown(): void
    {
        $property = new ReflectionProperty(AbstractManager::class, 'request');
        $property->setValue(null, null);
        parent::tearDown();
    }

    public function testAddFieldGetValueAndSave(): void
    {
        $this->manager->addField('missing', 'post');
        $this->assertArrayNotHasKey('missing', $this->fields());

        $post = get_post($this->postId);
        $this->assertInstanceOf(WP_Post::class, $post);
        $this->setPost($post);
        update_post_meta($this->postId, 'subtitle', 'Saved');
        $this->assertSame('Saved', $this->manager->value('subtitle'));

        $this->manager->getRequest()->request->set('subtitle', 'Updated');
        $this->manager->saveField($this->field);
        $this->assertSame('Updated', get_post_meta($this->postId, 'subtitle', true));
        $this->assertTrue($this->manager->authorization($this->field));
    }

    public function testSaveSkipsRevisionsAndRendersTheBox(): void
    {
        $revision = wp_insert_post([
            'post_title' => 'Revision',
            'post_type' => 'revision',
            'post_parent' => $this->postId,
            'post_status' => 'inherit',
        ]);
        $method = new \ReflectionMethod(PostMetaManager::class, 'save');
        $method->invoke($this->manager, $revision, get_post($revision));
        $this->assertSame('', (string)get_post_meta($this->postId, 'subtitle', true));

        $box = new \ReflectionMethod(PostMetaManager::class, 'addMetaBox');
        $box->invoke($this->manager, get_post($this->postId));
        $this->assertNotEmpty($GLOBALS['wp_meta_boxes']['post'] ?? []);
    }

    private function fields(): array
    {
        return (new ReflectionProperty(PostMetaManager::class, 'fields'))->getValue($this->manager);
    }

    private function setPost(WP_Post $post): void
    {
        (new ReflectionProperty(PostMetaManager::class, 'post'))->setValue($this->manager, $post);
    }
}
