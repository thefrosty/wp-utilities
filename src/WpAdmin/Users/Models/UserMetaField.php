<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\WpAdmin\Users\Models;

use Closure;
use TheFrosty\WpUtilities\WpAdmin\Users\Fields\Type;
use TheFrosty\WpUtilities\Models\BaseModel;

/**
 * Class UserMetaField
 * @package TheFrosty\WpUtilities\WpAdmin\Users\Models
 */
class UserMetaField extends BaseModel
{
    final public const string FIELD_CONDITION = 'condition';
    final public const string FIELD_DESCRIPTION = 'description';
    final public const string FIELD_LABEL = 'label';
    final public const string FIELD_NAME = 'name';
    final public const string FIELD_TYPE = 'type';

    /**
     * Field condition.
     * @var Closure|null $condition
     */
    protected ?Closure $condition = null;

    /**
     * Field description.
     * @var string|null $description
     */
    protected ?string $description = null;

    /**
     * Field label.
     * @var string $label
     */
    protected string $label;

    /**
     * Field name.
     * @var string $name
     */
    protected string $name;

    /**
     * Field HTML input type.
     * @var Type $type
     */
    protected Type $type = Type::TEXT;

    /**
     * Get field condition.
     * @return Closure|null
     */
    public function getCondition(): ?Closure
    {
        return $this->condition;
    }

    /**
     * Set field condition.
     * @param Closure $condition
     */
    protected function setCondition(Closure $condition): void
    {
        $this->condition = $condition;
    }

    /**
     * Get field description.
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Set field description.
     * @param string $description
     */
    protected function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * Get field label.
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * Set field label.
     * @param string $label
     */
    protected function setLabel(string $label): void
    {
        $this->label = $label;
    }

    /**
     * Get field name.
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Set field name.
     * @param string $name
     */
    protected function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * Get field HTML input type.
     * @ref https://developer.mozilla.org/en-US/docs/Web/HTML/Element/input
     * @ref https://stitcher.io/blog/php-enums
     * @return Type
     */
    public function getType(): Type
    {
        return $this->type;
    }

    /**
     * Get field input type.
     * @param Type $type
     */
    protected function setType(Type $type): void
    {
        $this->type = $type;
    }

    /**
     * Get serializable fields.
     * @return string[]
     */
    protected function getSerializableFields(): array
    {
        return [
            self::FIELD_DESCRIPTION,
            self::FIELD_LABEL,
            self::FIELD_NAME,
            self::FIELD_TYPE,
        ];
    }
}
