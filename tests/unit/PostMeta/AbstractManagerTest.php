<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\PostMeta;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use TheFrosty\WpUtilities\PostMeta\AbstractManager;
use TheFrosty\WpUtilities\Tests\Plugin\Framework\TestCase;
use WP_Post;

/**
 * Class AbstractManagerTest
 * @package TheFrosty\WpUtilities\Tests\PostMeta
 */
#[CoversClass(AbstractManager::class)]
#[Group('post-meta')]
class AbstractManagerTest extends TestCase
{
    public function testAddHooksBailsWithoutPostFields(): void
    {
        $manager = new class([]) extends AbstractManager {
            public function addMetaBox(WP_Post $post): void
            {
            }

            public function save(int $post_id, WP_Post $post): void
            {
            }
        };

        $manager->addHooks();
        $this->assertFalse(has_action('add_meta_boxes_post'));
    }

    public function testAddHooksRegistersPostMetaHooks(): void
    {
        $manager = new class(['subtitle' => 'Subtitle']) extends AbstractManager {
            public function addMetaBox(WP_Post $post): void
            {
            }

            public function save(int $post_id, WP_Post $post): void
            {
            }
        };

        $manager->addHooks();
        $this->assertNotFalse(has_action('add_meta_boxes_post'));
        $this->assertNotFalse(has_action('save_post_post'));
    }
}
