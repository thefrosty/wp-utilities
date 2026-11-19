<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\WpAdmin\Columns;

use BadMethodCallException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\UsesClass;
use Symfony\Component\HttpFoundation\Request;
use TheFrosty\WpUtilities\Tests\Plugin\Framework\TestCase;
use TheFrosty\WpUtilities\WpAdmin\Columns\AbstractColumns;
use TheFrosty\WpUtilities\WpAdmin\Models\OptionValueLabel;
use TheFrosty\WpUtilities\WpAdmin\RestrictPostsInterface;

/**
 * Class AbstractColumnsTest
 * @package TheFrosty\WpUtilities\Tests\WpAdmin\Columns
 */
#[CoversClass(AbstractColumns::class)]
#[UsesClass(OptionValueLabel::class)]
#[Group('wp-admin')]
class AbstractColumnsTest extends TestCase
{
    public function testColumnHookNamesAndOptionValues(): void
    {
        $columns = new ColumnFixture();
        $columns->hooks('book');

        $this->assertSame('manage_book_posts_columns', $columns->filter());
        $this->assertSame('manage_book_posts_custom_column', $columns->contentAction());
        $this->assertNotFalse(has_filter('manage_book_posts_columns'));

        $postId = wp_insert_post(['post_title' => 'Column Post', 'post_status' => 'publish']);
        $options = [];
        $columns->options([[$postId], 'plain'], 'books', $options);

        $this->assertInstanceOf(OptionValueLabel::class, $options['books'][0]);
        $this->assertSame((string)$postId, $options['books'][0]->getValue());
        $this->assertSame('Column Post', $options['books'][0]->getLabel());
        $this->assertSame('plain', $options['books'][1]->getLabel());
    }

    public function testDeferredCacheSchedulesASingleEvent(): void
    {
        $columns = new ColumnFixture();
        $this->assertSame([], $columns->cached('book'));
        $scheduled = false;
        foreach (_get_cron_array() ?: [] as $hooks) {
            if (isset($hooks['book_wp_query_get_all_ids'])) {
                $scheduled = true;
                break;
            }
        }
        $this->assertTrue($scheduled);
    }

    public function testRequestHasRequiresARequest(): void
    {
        $columns = new ColumnFixture();
        $this->expectException(BadMethodCallException::class);
        $columns->hasRequest('isbn');
    }

    public function testRequestHasMatchesTheAdminFilter(): void
    {
        $request = Request::create('/', 'GET', [
            's' => 'query',
            RestrictPostsInterface::ADMIN_FILTER_FIELD_NAME => 'isbn',
        ]);
        $columns = new ColumnFixture();

        $this->assertTrue($columns->hasRequest('isbn', $request));
        $this->assertFalse($columns->hasRequest('other', $request));
    }
    public function testDeferredCacheReturnsCachedIdsAndTheCronCallbackPopulatesTheCache(): void
    {
        $columns = new ColumnFixture();
        $postId = wp_insert_post(['post_title' => 'Cached column post', 'post_status' => 'publish']);

        // The first call schedules the background cron event that populates the cache.
        $hook = 'post_wp_query_get_all_ids';
        $this->assertSame([], $columns->cached('post'));
        $key = null;
        foreach (_get_cron_array() ?: [] as $hooks) {
            foreach ($hooks[$hook] ?? [] as $event) {
                $key = $event['args'][0];
            }
        }
        $this->assertIsString($key);
        $columns->hooks('post');
        do_action($hook, $key, 'post', [], 60);

        $cached = $columns->cached('post');
        $this->assertContains($postId, $cached);
        wp_delete_post($postId, true);
    }

}

class ColumnFixture extends AbstractColumns
{
    public function addHooks(): void
    {
    }

    public function hooks(string $postType): void
    {
        $this->addColumnHooks($postType);
    }

    public function filter(): string
    {
        return $this->getColumnFilter('book');
    }

    public function contentAction(): string
    {
        return $this->getColumnContentAction('book');
    }

    public function options(array $data, string $key, array &$options): void
    {
        $this->setOptionKeyValues($data, $key, $options);
    }

    public function cached(string $postType): array
    {
        return $this->getCachedPostsDeferred($postType);
    }

    public function hasRequest(string $field, ?Request $request = null): bool
    {
        return $this->requestHas($field, $request);
    }

    protected function addPostTypeColumns(array $columns): array
    {
        return $columns;
    }

    protected function addPostTypeColumnContent(string $column, int $post_id): void
    {
    }
}
