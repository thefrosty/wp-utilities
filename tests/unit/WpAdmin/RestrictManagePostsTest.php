<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\WpAdmin;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\Attributes\UsesFunction;
use PHPUnit\Framework\Attributes\UsesTrait;
use ReflectionMethod;
use ReflectionProperty;
use Symfony\Component\HttpFoundation\Request;
use TheFrosty\WpUtilities\Plugin\Container;
use TheFrosty\WpUtilities\Plugin\HttpFoundationRequestTrait;
use TheFrosty\WpUtilities\Plugin\Init;
use TheFrosty\WpUtilities\Plugin\Plugin;
use TheFrosty\WpUtilities\Plugin\PluginFactory;
use TheFrosty\WpUtilities\Plugin\TemplateLoader;
use TheFrosty\WpUtilities\Tests\Plugin\Framework\TestCase;
use TheFrosty\WpUtilities\WpAdmin\Models\OptionValueLabel;
use TheFrosty\WpUtilities\WpAdmin\RestrictManagePosts;
use WP_Query;

/**
 * Class RestrictManagePostsTest
 * @package TheFrosty\WpUtilities\Tests\WpAdmin
 */
#[CoversClass(RestrictManagePosts::class)]
#[UsesTrait(HttpFoundationRequestTrait::class)]
#[UsesClass(Container::class)]
#[UsesClass(Init::class)]
#[UsesClass(OptionValueLabel::class)]
#[UsesClass(Plugin::class)]
#[UsesClass(PluginFactory::class)]
#[UsesClass(TemplateLoader::class)]
#[UsesFunction('TheFrosty\WpUtilities\wpRegisterScript')]
#[Group('wp-admin')]
class RestrictManagePostsTest extends TestCase
{
    private RestrictManagePosts $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->plugin = PluginFactory::create('restrict-posts', __FILE__);
        $this->manager = new RestrictManagePosts();
        $this->resetRequest();
        $this->manager->setPlugin($this->plugin);
        set_current_screen('edit-post');
    }

    protected function tearDown(): void
    {
        $this->resetRequest();
        parent::tearDown();
    }

    private function resetRequest(): void
    {
        $property = new ReflectionProperty(RestrictManagePosts::class, 'request');
        $property->setValue(null, null);
    }

    public function testFilteredMetaHelpersAndScriptTag(): void
    {
        add_filter(RestrictManagePosts::TAG_FILTER_META_KEYS, static fn(): array => ['color' => []]);
        add_filter(RestrictManagePosts::TAG_FILTER_META_VALUES, static fn(): string => 'nope');

        $this->assertSame(['color' => []], RestrictManagePosts::getFilteredMetaKeys('post'));
        $this->assertSame([], RestrictManagePosts::getFilteredMetaValues('post'));

        $tag = (new ReflectionMethod(RestrictManagePosts::class, 'modifyScriptType'))
            ->invoke($this->manager, '<script></script>', RestrictManagePosts::HANDLE_UTILITY_FUNCTIONS, 'https://example.com/functions.js');
        $this->assertStringContainsString("type='module'", $tag);
        $this->assertSame(
            '<script></script>',
            (new ReflectionMethod(RestrictManagePosts::class, 'modifyScriptType'))
                ->invoke($this->manager, '<script></script>', 'other', 'https://example.com/other.js')
        );
    }

    public function testManagePostsHtmlAndMetaQuery(): void
    {
        add_filter(RestrictManagePosts::TAG_FILTER_META_KEYS, static fn(): array => [
            'Keys' => [new OptionValueLabel('color', 'Color')],
        ]);
        add_filter(RestrictManagePosts::TAG_FILTER_META_VALUES, static fn(): array => [
            'Values' => [new OptionValueLabel('COMPARE:LIKE:blue', 'Blue')],
        ]);
        $this->manager->setRequest(Request::create('/wp-admin/edit.php', 'GET', [
            RestrictManagePosts::ADMIN_FILTER_FIELD_NAME => 'color',
            RestrictManagePosts::ADMIN_FILTER_FIELD_VALUE => 'COMPARE:LIKE:blue',
            RestrictManagePosts::ADMIN_SEARCH_FIELD_VALUE => '',
        ]));

        ob_start();
        (new ReflectionMethod(RestrictManagePosts::class, 'addManagePostsHtml'))->invoke($this->manager, 'post');
        $html = ob_get_clean();
        $this->assertStringContainsString('name="_filter_meta_key"', $html);

        global $pagenow, $wp_the_query;
        $pagenow = 'edit.php';
        $query = new WP_Query();
        $wp_the_query = $query;
        (new ReflectionMethod(RestrictManagePosts::class, 'preGetPostsFilterMetaQuery'))->invoke($this->manager, $query);

        $meta = $query->get('meta_query');
        $this->assertSame('color', $meta[0]['key']);
        $this->assertSame('blue', $meta[0]['value']);
        $this->assertSame('LIKE', $meta[0]['compare']);
    }

    public function testEmptySearchUsesNotExists(): void
    {
        $this->resetRequest();
        add_filter(RestrictManagePosts::TAG_FILTER_META_KEYS, static fn(): array => [
            'Keys' => [new OptionValueLabel('color', 'Color')],
        ]);
        $this->manager->setRequest(Request::create('/wp-admin/edit.php', 'GET', [
            RestrictManagePosts::ADMIN_FILTER_FIELD_NAME => 'color',
            RestrictManagePosts::ADMIN_FILTER_FIELD_VALUE => 'red',
            RestrictManagePosts::ADMIN_SEARCH_FIELD_VALUE => 'EMPTY',
        ]));

        global $pagenow, $wp_the_query;
        $pagenow = 'edit.php';
        $query = new WP_Query();
        $wp_the_query = $query;
        (new ReflectionMethod(RestrictManagePosts::class, 'preGetPostsFilterMetaQuery'))->invoke($this->manager, $query);

        $this->assertSame('NOT EXISTS', $query->get('meta_query')[0]['compare']);
    }

    public function testOnlyRegisteredMetaKeysCanBeFiltered(): void
    {
        remove_all_filters(RestrictManagePosts::TAG_FILTER_META_KEYS);
        add_filter(RestrictManagePosts::TAG_FILTER_META_KEYS, static fn(): array => [
            'Keys' => [new OptionValueLabel('color|extra', 'Color')],
        ]);
        $this->assertSame(['color'], RestrictManagePosts::getAllowedMetaKeys('post'));

        global $pagenow, $wp_the_query;
        $pagenow = 'edit.php';
        foreach (['_private_secret', 'color'] as $key) {
            $this->resetRequest();
            $this->manager->setRequest(Request::create('/wp-admin/edit.php', 'GET', [
                RestrictManagePosts::ADMIN_FILTER_FIELD_NAME => $key,
                RestrictManagePosts::ADMIN_FILTER_FIELD_VALUE => 'COMPARE:REGEXP:^a',
            ]));
            $query = new WP_Query();
            $wp_the_query = $query;
            (new ReflectionMethod(RestrictManagePosts::class, 'preGetPostsFilterMetaQuery'))
                ->invoke($this->manager, $query);

            if ($key === 'color') {
                $this->assertSame('color', $query->get('meta_query')[0]['key']);
                continue;
            }
            $this->assertEmpty($query->get('meta_query'), 'Unregistered meta keys must be ignored.');
        }
        remove_all_filters(RestrictManagePosts::TAG_FILTER_META_KEYS);
    }

    public function testAdvancedSearchUsesTheFirstMatchingPostId(): void
    {
        $this->resetRequest();
        $first = wp_insert_post(['post_title' => 'Aardvark Advanced Lookup', 'post_status' => 'publish']);
        $second = wp_insert_post(['post_title' => 'Zebra Advanced Lookup', 'post_status' => 'publish']);
        add_filter(RestrictManagePosts::TAG_FILTER_META_KEYS, static fn(): array => [
            'Keys' => [new OptionValueLabel('related_post', 'Related')],
        ]);

        global $pagenow, $wp_the_query;
        $pagenow = 'edit.php';
        $meta_values = [];
        foreach (['Advanced Lookup' => $first, 'Zebra Advanced Lookup' => $second] as $title => $expected) {
            $this->resetRequest();
            $this->manager->setRequest(Request::create('/wp-admin/edit.php', 'GET', [
                RestrictManagePosts::ADMIN_FILTER_FIELD_NAME => 'related_post',
                RestrictManagePosts::ADMIN_FILTER_FIELD_VALUE => '',
                RestrictManagePosts::ADMIN_SEARCH_FIELD_VALUE =>
                    sprintf('meta_key:"related_post" post_title:"%s" post_type:"post"', $title),
            ]));
            $query = new WP_Query();
            $wp_the_query = $query;
            (new ReflectionMethod(RestrictManagePosts::class, 'preGetPostsFilterMetaQuery'))
                ->invoke($this->manager, $query);
            $meta_values[$title] = [$expected, $query->get('meta_query')[0]['value'] ?? null];
        }
        remove_all_filters(RestrictManagePosts::TAG_FILTER_META_KEYS);
        wp_delete_post($first, true);
        wp_delete_post($second, true);

        // Two matches (ordered by title): the first is used, not the second.
        $this->assertSame($first, $meta_values['Advanced Lookup'][1]);
        // A single match: previously `null`.
        $this->assertSame($second, $meta_values['Zebra Advanced Lookup'][1]);
    }

    public function testAddHooks(): void
    {
        $this->manager->addHooks();

        $this->assertNotFalse(has_action('admin_enqueue_scripts'));
        $this->assertNotFalse(has_filter('script_loader_tag'));
        $this->assertNotFalse(has_action('restrict_manage_posts'));
        $this->assertNotFalse(has_action('pre_get_posts'));
    }

    public function testEnqueueScriptsOnlyOnTheEditScreenWhenEnabled(): void
    {
        $enqueue = new ReflectionMethod(RestrictManagePosts::class, 'enqueueScripts');
        wp_deregister_script(RestrictManagePosts::HANDLE);

        $enqueue->invoke($this->manager, 'index.php');
        $this->assertFalse(wp_script_is(RestrictManagePosts::HANDLE, 'registered'));

        add_filter(RestrictManagePosts::TAG_FILTER_ENABLE_SCRIPTS, '__return_false');
        $enqueue->invoke($this->manager, 'edit.php');
        remove_all_filters(RestrictManagePosts::TAG_FILTER_ENABLE_SCRIPTS);
        $this->assertFalse(wp_script_is(RestrictManagePosts::HANDLE, 'registered'));

        $enqueue->invoke($this->manager, 'edit.php');
        $this->assertTrue(wp_style_is('select2', 'enqueued'));
        $this->assertTrue(wp_script_is('select2', 'registered'));
        $this->assertTrue(wp_script_is(RestrictManagePosts::HANDLE_UTILITY_FUNCTIONS, 'registered'));
        $this->assertTrue(wp_script_is(RestrictManagePosts::HANDLE, 'enqueued'));
        $this->assertSame(
            ['select2', RestrictManagePosts::HANDLE_UTILITY_FUNCTIONS],
            wp_scripts()->registered[RestrictManagePosts::HANDLE]->deps
        );
    }

    public function testMetaQueryIsSkippedOutsideTheEditScreen(): void
    {
        $this->resetRequest();
        $this->manager->setRequest(Request::create('/wp-admin/index.php', 'GET', [
            RestrictManagePosts::ADMIN_FILTER_FIELD_NAME => 'color',
            RestrictManagePosts::ADMIN_FILTER_FIELD_VALUE => 'red',
        ]));

        global $pagenow, $wp_the_query;
        $pagenow = 'index.php';
        $query = new WP_Query();
        $wp_the_query = $query;
        (new ReflectionMethod(RestrictManagePosts::class, 'preGetPostsFilterMetaQuery'))->invoke($this->manager, $query);

        $this->assertEmpty($query->get('meta_query'));
    }

    public function testSearchValueFiltersByTheSelectedMetaKey(): void
    {
        $this->resetRequest();
        add_filter(RestrictManagePosts::TAG_FILTER_META_KEYS, static fn(): array => [
            'Keys' => [new OptionValueLabel('color', 'Color')],
        ]);
        $this->manager->setRequest(Request::create('/wp-admin/edit.php', 'GET', [
            RestrictManagePosts::ADMIN_FILTER_FIELD_NAME => 'color',
            RestrictManagePosts::ADMIN_FILTER_FIELD_VALUE => '',
            RestrictManagePosts::ADMIN_SEARCH_FIELD_VALUE => '<b>teal</b>',
        ]));

        global $pagenow, $wp_the_query;
        $pagenow = 'edit.php';
        $query = new WP_Query();
        $wp_the_query = $query;
        (new ReflectionMethod(RestrictManagePosts::class, 'preGetPostsFilterMetaQuery'))->invoke($this->manager, $query);
        remove_all_filters(RestrictManagePosts::TAG_FILTER_META_KEYS);

        $this->assertSame([['key' => 'color', 'value' => 'teal']], $query->get('meta_query'));
    }

    public function testAdvancedSearchFiltersByAPostFoundByTitle(): void
    {
        $this->resetRequest();
        $post_id = wp_insert_post(['post_title' => 'Advanced Search Target', 'post_status' => 'publish']);
        add_filter(RestrictManagePosts::TAG_FILTER_META_KEYS, static fn(): array => [
            'Keys' => [new OptionValueLabel('related_post', 'Related')],
        ]);
        $this->manager->setRequest(Request::create('/wp-admin/edit.php', 'GET', [
            RestrictManagePosts::ADMIN_FILTER_FIELD_NAME => 'related_post',
            RestrictManagePosts::ADMIN_FILTER_FIELD_VALUE => '',
            RestrictManagePosts::ADMIN_SEARCH_FIELD_VALUE =>
                'meta_key:"related_post" post_title:"Advanced Search Target" post_type:"post"',
        ]));

        global $pagenow, $wp_the_query;
        $pagenow = 'edit.php';
        $query = new WP_Query();
        $wp_the_query = $query;
        (new ReflectionMethod(RestrictManagePosts::class, 'preGetPostsFilterMetaQuery'))->invoke($this->manager, $query);
        remove_all_filters(RestrictManagePosts::TAG_FILTER_META_KEYS);
        wp_delete_post($post_id, true);

        $meta_query = $query->get('meta_query');
        $this->assertSame('related_post', $meta_query[0]['key']);
        $this->assertSame($post_id, $meta_query[0]['value']);
    }
}
