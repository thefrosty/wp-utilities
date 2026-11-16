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
}
