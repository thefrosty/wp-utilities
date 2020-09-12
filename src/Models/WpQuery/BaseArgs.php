<?php declare(strict_types=1);

namespace TheFrosty\WpUtilities\Models\WpQuery;

/**
 * Class Base
 * @package TheFrosty\WpUtilities\Models\WpQuery
 */
class BaseArgs implements \ArrayAccess
{
    /**
     * @param array $args
     * @return static
     */
    public static function fromArray(array $args): self
    {
        $class = new self();
        foreach ($args as $key => $value) {
            $class->$key = $value;
        }

        return $class;
    }

    /**
     * Convert all object vars into an array.
     * @return array<mixed, mixed>
     */
    public function toArray(): array
    {
        return \array_filter(\get_object_vars($this), static function ($value): bool {
            return $value !== null;
        });
    }

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return \array_key_exists($offset, \get_object_vars($this));
    }

    /**
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        if (!\array_key_exists($offset, \get_object_vars($this))) {
            return null;
        }

        return $this->$offset;
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value): void
    {
        $this->$offset = $value;
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset): void
    {
        unset($this->$offset);
    }
}
