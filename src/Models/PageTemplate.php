<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Models;

/**
 * Class PageTemplate
 * @package TheFrosty\WpUtilities\Models
 */
class PageTemplate extends BaseModel
{

    public const FIELD_DESC = 'description';
    public const FIELD_FILE = 'file';
    public const FIELD_PATH = 'path';

    /**
     * Template description (example: "Awesome Template").
     * @var string $description
     */
    private string $description;

    /**
     * Template path basename (example: "template-awesome.php").
     * @var string $file
     */
    private string $file;

    /**
     * Template path location (example: "/full/path_to/template-awesome.php").
     * @var string $path
     */
    private string $path;

    public function getDescription(): string
    {
        return $this->description;
    }

    protected function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getFile(): string
    {
        return $this->file;
    }

    protected function setFile(string $file): void
    {
        $this->file = $file;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    protected function setPath(string $path): void
    {
        $this->path = $path;
    }

    /**
     * Get serializable fields.
     * @return string[]
     */
    protected function getSerializableFields(): array
    {
        return [
            self::FIELD_DESC,
            self::FIELD_FILE,
            self::FIELD_PATH,
        ];
    }
}
