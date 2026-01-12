<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\PostMeta\Fields;

/**
 * Interface Field
 * @package TheFrosty\WpUtilities\PostMeta\Fields
 */
interface Field
{

    public function getId(): string;

    public function setId(string $id): void;

    public function getLabel(): string;

    public function setLabel(string $label): void;

    public function getType(): string;

    public function setType(string $type): void;

    public function authorization(): bool;

    public function sanitize($value): mixed;

    public function value(): mixed;

    public function save(): void;
}
