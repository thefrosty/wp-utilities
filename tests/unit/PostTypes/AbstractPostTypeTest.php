<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\PostTypes;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PostTypes\PostType;
use TheFrosty\WpUtilities\Exceptions\Exception;
use TheFrosty\WpUtilities\Plugin\AbstractPlugin;
use TheFrosty\WpUtilities\Plugin\Init;
use TheFrosty\WpUtilities\PostTypes\AbstractPostType;
use TheFrosty\WpUtilities\Tests\Plugin\Framework\TestCase;

/**
 * Class AbstractPostTypeTest
 * @package TheFrosty\WpUtilities\Tests\PostTypes
 */
#[CoversClass(AbstractPlugin::class)]
#[CoversClass(AbstractPostType::class)]
#[CoversClass(Init::class)]
#[Group('post-types')]
class AbstractPostTypeTest extends TestCase
{
    public function testConstructorRequiresConstantsAndProperties(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Undefined constants.');
        new class extends AbstractPostType {
            public function addHooks(): void
            {
            }
        };
    }

    public function testRegisterPostType(): void
    {
        $type = new class extends AbstractPostType {
            public const array|string|null POST_TYPE = 'book';
            public const string|null SLUG = 'book';

            public function __construct()
            {
                $this->setNames(['singular' => 'Book', 'plural' => 'Books']);
                $this->setArgs([]);
                $this->setLabels(['name' => 'Books']);
                parent::__construct();
            }

            public function addHooks(): void
            {
            }

            public function expose(): PostType
            {
                return $this->registerPostType();
            }
        };

        $registered = $type->expose();
        do_action('init');
        $this->assertInstanceOf(PostType::class, $registered);
        $this->assertNotNull(get_post_type_object('book'));
    }

    public function testConstructorRequiresTheClassProperties(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Required class properties not set.');
        new class extends AbstractPostType {
            public const array|string|null POST_TYPE = 'book_missing_props';
            public const string|null SLUG = 'book_missing_props';

            public function addHooks(): void
            {
            }
        };
    }

    public function testBuildBase64DataImageFromTheSvg(): void
    {
        $type = new class extends AbstractPostType {
            public const array|string|null POST_TYPE = 'book_icon';
            public const string|null SLUG = 'book_icon';
            public const string|null URL_SLUG = 'books';

            public function __construct()
            {
                $this->setNames(['singular' => 'Book', 'plural' => 'Books']);
                $this->setArgs([]);
                $this->setLabels(['name' => 'Books']);
                parent::__construct();
            }

            public function addHooks(): void
            {
            }

            public function icon(): string
            {
                return $this->buildBase64DataImage();
            }

            public function args(): array
            {
                return $this->getArgs();
            }

            protected function getSvg(): string
            {
                return '<svg viewBox="0 0 20 20"><path d="M0 0h20v20H0z"/></svg>';
            }
        };

        $icon = $type->icon();
        $this->assertStringStartsWith('data:image/svg+xml;base64, ', $icon);
        $this->assertSame(
            '<svg width="20" height="20" viewBox="0 0 20 20"><path fill="black" d="M0 0h20v20H0z"/></svg>',
            base64_decode(substr($icon, strlen('data:image/svg+xml;base64, ')))
        );
        // URL_SLUG overrides the slug derived from SLUG.
        $this->assertSame('books', $type->args()['rewrite']['slug']);
    }

    public function testTheDefaultSvgIsEmpty(): void
    {
        $type = new class extends AbstractPostType {
            public const array|string|null POST_TYPE = 'book_no_icon';
            public const string|null SLUG = 'book_no_icon';

            public function __construct()
            {
                $this->setNames(['singular' => 'Book', 'plural' => 'Books']);
                $this->setArgs([]);
                $this->setLabels(['name' => 'Books']);
                parent::__construct();
            }

            public function addHooks(): void
            {
            }

            public function icon(): string
            {
                return $this->buildBase64DataImage();
            }
        };

        $this->assertSame('data:image/svg+xml;base64, ', $type->icon());
    }
}
