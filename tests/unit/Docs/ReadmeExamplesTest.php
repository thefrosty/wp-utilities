<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\Docs;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\UsesClass;
use TheFrosty\WpUtilities\PostTypes\AbstractPostType;
use TheFrosty\WpUtilities\Taxonomies\AbstractTaxonomy;
use TheFrosty\WpUtilities\Tests\Plugin\Framework\TestCase;
use TheFrosty\WpUtilities\Utils\ObjectNames;
use WP_Post_Type;
use WP_Taxonomy;

/**
 * Class ReadmeExamplesTest
 * Registers the "Quick start" examples from src/PostTypes/README.md and src/Taxonomies/README.md and checks the
 * values those READMEs document, so the docs can't drift from the code.
 * @package TheFrosty\WpUtilities\Tests\Docs
 */
#[CoversClass(AbstractPostType::class)]
#[CoversClass(AbstractTaxonomy::class)]
#[UsesClass(ObjectNames::class)]
#[Group('docs')]
class ReadmeExamplesTest extends TestCase
{
    protected function tearDown(): void
    {
        unregister_taxonomy(Genre::TAXONOMY_TYPE);
        unregister_post_type(Book::POST_TYPE);
        parent::tearDown();
    }

    public function testPostTypeQuickStartMatchesTheReadme(): void
    {
        new Book()->addHooks();

        $book = get_post_type_object(Book::POST_TYPE);
        $this->assertInstanceOf(WP_Post_Type::class, $book);

        // "Labels" table.
        $this->assertSame('Books', $book->labels->name);
        $this->assertSame('Books', $book->labels->all_items);
        $this->assertSame('Book', $book->labels->singular_name);
        $this->assertSame('Library', $book->labels->menu_name);
        $this->assertSame('Add New', $book->labels->add_new);
        $this->assertSame('Add New Book', $book->labels->add_new_item);
        $this->assertSame('Edit Book', $book->labels->edit_item);
        $this->assertSame('New Book', $book->labels->new_item);
        $this->assertSame('View Book', $book->labels->view_item);
        $this->assertSame('Search Books', $book->labels->search_items);
        $this->assertSame('No Books found', $book->labels->not_found);
        $this->assertSame('No Books found in Trash', $book->labels->not_found_in_trash);
        $this->assertSame('Parent Book:', $book->labels->parent_item_colon);

        // "Default args" & "Slugs".
        $this->assertTrue($book->public);
        $this->assertTrue($book->show_in_rest);
        $this->assertSame('book', $book->rest_base);
        $this->assertSame('book', $book->rewrite['slug']);
        $this->assertTrue((bool)$book->has_archive);
        $this->assertFalse($book->show_in_nav_menus);
        $this->assertTrue(post_type_supports(Book::POST_TYPE, 'editor'));
        $this->assertTrue(post_type_supports(Book::POST_TYPE, 'thumbnail'));
    }

    public function testTaxonomyQuickStartMatchesTheReadme(): void
    {
        new Book()->addHooks();
        $genre = new Genre();
        $genre->addHooks();

        $taxonomy = get_taxonomy(Genre::TAXONOMY_TYPE);
        $this->assertInstanceOf(WP_Taxonomy::class, $taxonomy);
        $this->assertSame([Book::POST_TYPE], $taxonomy->object_type);
        $this->assertTrue($taxonomy->hierarchical);

        // "Labels" table.
        $this->assertSame('Genres', $taxonomy->labels->name);
        $this->assertSame('Genres', $taxonomy->labels->menu_name);
        $this->assertSame('Genre', $taxonomy->labels->singular_name);
        $this->assertSame('All Genres', $taxonomy->labels->all_items);
        $this->assertSame('Update Genre', $taxonomy->labels->update_item);
        $this->assertSame('New Genre Name', $taxonomy->labels->new_item_name);
        $this->assertSame('Parent Genres:', $taxonomy->labels->parent_item_colon);
        $this->assertSame('Separate Genres with commas', $taxonomy->labels->separate_items_with_commas);
        $this->assertSame('No Genres found', $taxonomy->labels->not_found);

        // "Default args", "Slugs" & "Terms as constants".
        $this->assertSame('bookGenre', $taxonomy->rewrite['slug']);
        $this->assertSame('bookGenre', $taxonomy->rest_base);
        $this->assertTrue($taxonomy->show_admin_column);
        $this->assertFalse($taxonomy->meta_box_cb);
        $this->assertSame(
            ['TERM_FICTION' => 'fiction', 'TERM_HISTORY' => 'history'],
            AbstractTaxonomy::getTerms($genre)
        );
    }

    public function testDerivedNamesMatchTheReadme(): void
    {
        $this->assertSame('Book Review', ObjectNames::singular(['name' => 'book_review']));
        $this->assertSame('Book Reviews', ObjectNames::plural(['name' => 'book_review']));
        $this->assertSame('Stories', ObjectNames::plural(['name' => 'story']));
        $this->assertSame('Categories', ObjectNames::plural(['name' => 'category']));
    }
}
