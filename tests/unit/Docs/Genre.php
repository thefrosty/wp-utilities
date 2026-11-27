<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\Docs;

use TheFrosty\WpUtilities\Plugin\WpHooksInterface;
use TheFrosty\WpUtilities\Taxonomies\AbstractTaxonomy;

/**
 * Class Genre
 * The "Quick start" taxonomy from src/Taxonomies/README.md (keep them in sync).
 * @package TheFrosty\WpUtilities\Tests\Docs
 */
class Genre extends AbstractTaxonomy implements WpHooksInterface
{
    public const string|null TAXONOMY_TYPE = 'genre';
    public const array|string|null POST_TYPE = 'book'; // Or ['book', 'post'].
    public const string|null SLUG = 'book_genre';

    public const string TERM_FICTION = 'fiction';
    public const string TERM_HISTORY = 'history';

    public function __construct()
    {
        $this->setNames(['singular' => 'Genre', 'plural' => 'Genres']);
        $this->setArgs(['hierarchical' => true]);
        $this->setLabels(['menu_name' => 'Genres']);
        parent::__construct();
    }

    public function addHooks(): void
    {
        $this->registerTaxonomy();
    }
}
