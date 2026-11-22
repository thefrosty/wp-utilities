<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\Taxonomies;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\Attributes\Group;
use TheFrosty\WpUtilities\Exceptions\Exception;
use TheFrosty\WpUtilities\Plugin\AbstractPlugin;
use TheFrosty\WpUtilities\Plugin\Init;
use WP_Taxonomy;
use TheFrosty\WpUtilities\Taxonomies\AbstractTaxonomy;
use TheFrosty\WpUtilities\Utils\ObjectNames;
use TheFrosty\WpUtilities\Tests\Plugin\Framework\TestCase;

/**
 * Class AbstractTaxonomyTest
 * @package TheFrosty\WpUtilities\Tests\Taxonomies
 */
#[CoversClass(AbstractPlugin::class)]
#[CoversClass(AbstractTaxonomy::class)]
#[UsesClass(ObjectNames::class)]
#[CoversClass(Init::class)]
#[Group('taxonomies')]
class AbstractTaxonomyTest extends TestCase
{
    public function testConstructorRequiresConstants(): void
    {
        $this->expectException(Exception::class);
        new class extends AbstractTaxonomy {
        };
    }

    public function testRegistersTheTaxonomyAndListsTermConstants(): void
    {
        $taxonomy = new class extends AbstractTaxonomy {
            public const string|null TAXONOMY_TYPE = 'genre';
            public const array|string|null POST_TYPE = 'book';
            public const string|null SLUG = 'book_genre';
            public const string TERM_FICTION = 'fiction';

            public function __construct()
            {
                $this->setNames(['singular' => 'Genre', 'plural' => 'Genres']);
                $this->setArgs([]);
                $this->setLabels(['name' => 'Genres']);
                parent::__construct();
            }

            public function expose(): void
            {
                $this->registerTaxonomy();
            }
        };

        $this->assertSame(['TERM_FICTION' => 'fiction'], AbstractTaxonomy::getTerms($taxonomy));
        $taxonomy->expose();
        $object = get_taxonomy('genre');
        $this->assertInstanceOf(WP_Taxonomy::class, $object);
        $this->assertSame(['book'], $object->object_type);
        $this->assertSame('Genres', $object->labels->name);
        $this->assertSame('Add New Genre', $object->labels->add_new_item);
        $this->assertSame('Separate Genres with commas', $object->labels->separate_items_with_commas);
        $this->assertSame('bookGenre', $object->rewrite['slug']);

        // Before `init`, registration is deferred to `init` priority 9 (before post types).
        $fired = $GLOBALS['wp_actions']['init'];
        unset($GLOBALS['wp_actions']['init']);
        $taxonomy->expose();
        $GLOBALS['wp_actions']['init'] = $fired;
        $this->assertSame(9, has_action('init', [$taxonomy, 'registerTaxonomyNow']));
        remove_action('init', [$taxonomy, 'registerTaxonomyNow']);
        unregister_taxonomy('genre');
    }

    public function testConstructorRequiresTheClassProperties(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Required class properties not set.');
        new class extends AbstractTaxonomy {
            public const string|null TAXONOMY_TYPE = 'genre_missing_props';
            public const array|string|null POST_TYPE = 'book';
            public const string|null SLUG = 'genre_missing_props';
        };
    }

    public function testUrlSlugOverridesTheDerivedSlug(): void
    {
        $taxonomy = new class extends AbstractTaxonomy {
            public const string|null TAXONOMY_TYPE = 'genre_url_slug';
            public const array|string|null POST_TYPE = 'book';
            public const string|null SLUG = 'book_genre';
            public const string|null URL_SLUG = 'genres';

            public function __construct()
            {
                $this->setNames(['singular' => 'Genre', 'plural' => 'Genres']);
                $this->setArgs([]);
                $this->setLabels(['name' => 'Genres']);
                parent::__construct();
            }

            public function args(): array
            {
                return $this->getArgs();
            }
        };

        $this->assertSame('genres', $taxonomy->args()['rewrite']['slug']);
    }
}
