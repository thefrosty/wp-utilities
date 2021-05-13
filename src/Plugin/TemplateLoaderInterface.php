<?php declare(strict_types=1);

namespace TheFrosty\WpUtilities\Plugin;

/**
 * Interface TemplateLoaderInterface
 *
 * @package TheFrosty\WpUtilities\Plugin
 */
interface TemplateLoaderInterface
{

    /**
     * Return a template part.
     *
     * @param string $slug Template slug.
     * @param string|null $name Optional. Template variation name. Default null.
     *
     * @return string URI string to the template path file.
     * @throws \Exception
     */
    public function getTemplatePart(string $slug, ?string $name = null): string;

    /**
     * Retrieve a template part.
     *
     * @param string $slug Template slug.
     * @param string|null $name Optional. Template variation name. Default null.
     *
     * @throws \Exception
     */
    public function loadTemplatePart(string $slug, ?string $name = null): void;

    /**
     * Make custom data available to template.
     *
     * Data is available to the template as properties under the variable passed to '$var_name'.
     *
     * @param array $data Custom data for the template.
     * @param string|null $var The default var name.
     *
     * @return $this
     */
    public function setTemplateData(array $data = [], ?string $var = null): self;
}
