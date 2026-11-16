<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\Models;

use DateTime;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use RuntimeException;
use TheFrosty\WpUtilities\Models\BaseModel;
use TheFrosty\WpUtilities\Tests\Plugin\Framework\TestCase;

/**
 * Class BaseModelTest
 * @package TheFrosty\WpUtilities\Tests\Models
 */
#[CoversClass(BaseModel::class)]
#[Group('models')]
class BaseModelTest extends TestCase
{
    public function testConstructorIgnoresAMissingFieldList(): void
    {
        $model = new EmptyBaseModel();

        $this->assertSame([], $model->getCustomDelimiters());
    }

    public function testToArrayThrowsWhenNothingIsSerializable(): void
    {
        $this->expectException(RuntimeException::class);
        (new EmptyBaseModel())->toArray();
    }

    public function testPopulateAndToArray(): void
    {
        $child = new BaseModelFixture(['title' => 'Child']);
        $model = new BaseModelFixture([
            'title' => 'Parent',
            'ignored' => null,
            'missing' => 'skipped',
            'published' => '2026-11-16',
            'child' => $child,
            'children' => [new BaseModelFixture(['title' => 'One'])],
            'custom-field' => 'custom',
        ]);

        $this->assertSame(
            [
                'title' => 'Parent',
                'published' => $model->publishedAt(),
                'child' => [
                    'title' => 'Child',
                    'published' => null,
                    'child' => null,
                    'children' => [],
                    'custom-field' => '',
                ],
                'children' => [[
                    'title' => 'One',
                    'published' => null,
                    'child' => null,
                    'children' => [],
                    'custom-field' => '',
                ]],
                'custom-field' => 'custom',
            ],
            $model->toArray()
        );
        $this->assertInstanceOf(DateTime::class, $model->publishedAt());
        $this->assertSame(
            [[
                'title' => 'One',
                'published' => null,
                'child' => null,
                'children' => [],
                'custom-field' => '',
            ]],
            $model->toArrayDeep([new BaseModelFixture(['title' => 'One'])])
        );
    }
}

class EmptyBaseModel extends BaseModel
{
}

class BaseModelFixture extends BaseModel
{
    private string $title = '';

    private ?DateTime $published = null;

    private ?BaseModel $child = null;

    /** @var BaseModel[] */
    private array $children = [];

    private string $customField = '';

    public function publishedAt(): ?DateTime
    {
        return $this->published;
    }

    public function getCustomDelimiters(): array
    {
        return ['-'];
    }

    protected function getSerializableFields(): array
    {
        return ['title', 'published', 'child', 'children', 'custom-field'];
    }

    protected function getDateTimeFields(): array
    {
        return ['published'];
    }

    protected function setTitle(string $title): void
    {
        $this->title = $title;
    }

    protected function getTitle(): string
    {
        return $this->title;
    }

    protected function setPublished(DateTime $published): void
    {
        $this->published = $published;
    }

    protected function getPublished(): ?DateTime
    {
        return $this->published;
    }

    protected function setChild(BaseModel $child): void
    {
        $this->child = $child;
    }

    protected function getChild(): ?BaseModel
    {
        return $this->child;
    }

    protected function setChildren(array $children): void
    {
        $this->children = $children;
    }

    protected function getChildren(): array
    {
        return $this->children;
    }

    protected function populateCustomField(string $value): void
    {
        $this->customField = $value;
    }

    protected function getCustomField(): string
    {
        return $this->customField;
    }
}
