<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\Docs;

use TheFrosty\WpUtilities\PostTypes\AbstractPostType;

/**
 * Class Book
 * The "Quick start" post type from src/PostTypes/README.md (keep them in sync).
 * @package TheFrosty\WpUtilities\Tests\Docs
 */
class Book extends AbstractPostType
{
    public const array|string|null POST_TYPE = 'book';
    public const string|null SLUG = 'book';

    public function __construct()
    {
        $this->setNames(['singular' => 'Book', 'plural' => 'Books']);
        $this->setArgs(['supports' => ['title', 'editor', 'thumbnail']]);
        $this->setLabels(['menu_name' => 'Library']);
        parent::__construct();
    }

    public function addHooks(): void
    {
        $this->registerPostType();
    }
}
