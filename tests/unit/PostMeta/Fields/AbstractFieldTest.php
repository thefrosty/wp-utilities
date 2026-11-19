<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\PostMeta\Fields;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\Attributes\UsesTrait;
use ReflectionProperty;
use TheFrosty\WpUtilities\Models\BaseModel;
use TheFrosty\WpUtilities\Plugin\HttpFoundationRequestTrait;
use TheFrosty\WpUtilities\PostMeta\AbstractManager;
use TheFrosty\WpUtilities\PostMeta\Fields\AbstractField;
use TheFrosty\WpUtilities\PostMeta\FieldsRegistrar;
use TheFrosty\WpUtilities\PostMeta\PostMetaManager;
use TheFrosty\WpUtilities\Tests\Plugin\Framework\TestCase;

/**
 * Class AbstractFieldTest
 * @package TheFrosty\WpUtilities\Tests\PostMeta\Fields
 */
#[CoversClass(AbstractField::class)]
#[UsesClass(AbstractManager::class)]
#[UsesClass(BaseModel::class)]
#[UsesClass(FieldsRegistrar::class)]
#[UsesClass(PostMetaManager::class)]
#[UsesTrait(HttpFoundationRequestTrait::class)]
#[Group('post-meta')]
class AbstractFieldTest extends TestCase
{
    protected function tearDown(): void
    {
        (new ReflectionProperty(AbstractManager::class, 'request'))->setValue(null, null);
        wp_set_current_user(0);
        parent::tearDown();
    }

    public function testAccessorsAndManagerDelegation(): void
    {
        wp_set_current_user(1);
        $post_id = wp_insert_post(['post_title' => 'Abstract field', 'post_status' => 'publish']);
        $field = new class (['id' => 'wpu_abstract', 'label' => 'Abstract']) extends AbstractField {
            public function render(): void
            {
            }
        };
        $field->setType('post');
        $manager = new PostMetaManager('wpu_box', 'Box', ['wpu_abstract' => $field]);
        $manager->setRequest();
        (new ReflectionProperty(PostMetaManager::class, 'post'))->setValue($manager, get_post($post_id));
        $field->manager = $manager;

        $this->assertSame('wpu_abstract', $field->getId());
        $this->assertSame('Abstract', $field->getLabel());
        $this->assertSame('post', $field->getType());
        $this->assertSame(['raw'], $field->sanitize(['raw']), 'The base field does not alter values.');
        $this->assertTrue($field->authorization());

        $manager->getRequest()->request->set('wpu_abstract', 'Saved through the field');
        $field->save();
        $this->assertSame('Saved through the field', $field->value());
        wp_delete_post($post_id, true);
    }
}
