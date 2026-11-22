<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\Utils;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use TheFrosty\WpUtilities\Tests\Plugin\Framework\TestCase;
use TheFrosty\WpUtilities\Utils\ObjectNames;

/**
 * Class ObjectNamesTest
 * @package TheFrosty\WpUtilities\Tests\Utils
 */
#[CoversClass(ObjectNames::class)]
#[Group('utils')]
class ObjectNamesTest extends TestCase
{
    public static function names(): array
    {
        return [
            'explicit names win' => [
                ['name' => 'book', 'singular' => 'Novel', 'plural' => 'Novels'],
                'Novel',
                'Novels',
            ],
            'derived from name' => [['name' => 'book'], 'Book', 'Books'],
            'snake and kebab case' => [['name' => 'book_genre-type'], 'Book Genre Type', 'Book Genre Types'],
            'y becomes ies' => [['name' => 'category'], 'Category', 'Categories'],
            'mixed case is normalised' => [['name' => 'BOOK_genre'], 'Book Genre', 'Book Genres'],
            'no name' => [[], '', ''],
        ];
    }

    #[DataProvider('names')]
    public function testSingularAndPlural(array $names, string $singular, string $plural): void
    {
        $this->assertSame($singular, ObjectNames::singular($names));
        $this->assertSame($plural, ObjectNames::plural($names));
    }
}
