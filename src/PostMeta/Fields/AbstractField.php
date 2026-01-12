<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\PostMeta\Fields;

use TheFrosty\WpUtilities\Models\BaseModel;
use TheFrosty\WpUtilities\PostMeta\PostMetaManager;

/**
 * Class AbstractField
 * @package TheFrosty\WpUtilities\PostMeta\Fields
 */
abstract class AbstractField extends BaseModel implements Field, Render
{

    protected string $id;

    protected string $label;

    protected string $object_type;

    public PostMetaManager $manager;

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setLabel(string $label): void
    {
        $this->label = $label;
    }

    public function getType(): string
    {
        return $this->object_type;
    }

    public function setType(string $type): void
    {
        $this->object_type = $type;
    }

    public function authorization(): bool
    {
        return $this->manager->authorization($this);
    }

    public function sanitize($value): mixed
    {
        return $value;
    }

    public function value(): mixed
    {
        return $this->manager->value($this->id);
    }

    public function save(): void
    {
        $this->manager->saveField($this);
    }
}
