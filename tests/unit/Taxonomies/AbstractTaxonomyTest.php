<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\Taxonomies;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PostTypes\Taxonomy;
use TheFrosty\WpUtilities\Exceptions\Exception;
use TheFrosty\WpUtilities\Plugin\AbstractPlugin;
use TheFrosty\WpUtilities\Plugin\Init;
use WP_Taxonomy;
use TheFrosty\WpUtilities\Taxonomies\AbstractTaxonomy;
use TheFrosty\WpUtilities\Tests\Plugin\Framework\TestCase;

/**
 * Class AbstractTaxonomyTest
 * @package TheFrosty\WpUtilities\Tests\Taxonomies
 */
#[CoversClass(AbstractPlugin::class)]
#[CoversClass(AbstractTaxonomy::class)]
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

            public function expose(): Taxonomy
            {
                return $this->registerTaxonomy();
            }
        };

        $this->assertSame(['TERM_FICTION' => 'fiction'], AbstractTaxonomy::getTerms($taxonomy));
        $this->assertInstanceOf(Taxonomy::class, $taxonomy->expose());
        do_action('init');
        $this->assertInstanceOf(WP_Taxonomy::class, get_taxonomy('genre'));
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
