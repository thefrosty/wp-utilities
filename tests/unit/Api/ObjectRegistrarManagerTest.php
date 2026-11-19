<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\Api;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\Attributes\UsesTrait;
use TheFrosty\WpUtilities\Api\Instantiate;
use TheFrosty\WpUtilities\Api\ObjectRegistrarManager;
use TheFrosty\WpUtilities\Plugin\Container;
use TheFrosty\WpUtilities\Plugin\HooksTrait;
use TheFrosty\WpUtilities\Plugin\Init;
use TheFrosty\WpUtilities\Plugin\Plugin;
use TheFrosty\WpUtilities\Plugin\PluginAwareTrait;
use TheFrosty\WpUtilities\Plugin\PluginFactory;
use TheFrosty\WpUtilities\Plugin\TemplateLoader;
use TheFrosty\WpUtilities\PostTypes\Columns\Api\ColumnsRegistrar;
use TheFrosty\WpUtilities\PostTypes\CustomFields\Api\CustomFieldsRegistrar;
use TheFrosty\WpUtilities\PostTypes\PostTypesRegistrar;
use TheFrosty\WpUtilities\Taxonomies\TaxonomyRegistrar;
use TheFrosty\WpUtilities\Tests\PlainObject;
use TheFrosty\WpUtilities\Tests\Plugin\Framework\TestCase;

/**
 * Class ObjectRegistrarManagerTest
 * Covers the shared registrar manager and each of its concrete (abstract) registrars.
 * @package TheFrosty\WpUtilities\Tests\Api
 */
#[CoversClass(ObjectRegistrarManager::class)]
#[CoversClass(ColumnsRegistrar::class)]
#[CoversClass(CustomFieldsRegistrar::class)]
#[CoversClass(PostTypesRegistrar::class)]
#[CoversClass(TaxonomyRegistrar::class)]
#[UsesClass(Container::class)]
#[UsesClass(Init::class)]
#[UsesClass(Plugin::class)]
#[UsesClass(PluginFactory::class)]
#[UsesClass(TemplateLoader::class)]
#[UsesTrait(HooksTrait::class)]
#[UsesTrait(Instantiate::class)]
#[UsesTrait(PluginAwareTrait::class)]
#[Group('api')]
class ObjectRegistrarManagerTest extends TestCase
{
    public static function registrars(): array
    {
        return [
            'columns' => [ColumnsRegistrar::class, ColumnsRegistrar::TAG_COLUMNS_MANAGER_REGISTRAR],
            'custom fields' => [
                CustomFieldsRegistrar::class,
                CustomFieldsRegistrar::TAG_CUSTOM_FIELDS_MANAGER_REGISTRAR,
            ],
            'post types' => [PostTypesRegistrar::class, PostTypesRegistrar::TAG_POST_TYPE_MANAGER_REGISTRAR],
            'taxonomies' => [TaxonomyRegistrar::class, TaxonomyRegistrar::TAG_TAXONOMY_MANAGER_REGISTRAR],
        ];
    }

    #[DataProvider('registrars')]
    public function testRegistrarInstantiatesTheRegisteredClasses(string $class, string $tag): void
    {
        remove_all_filters($tag);
        $plugin = PluginFactory::create('registrar-' . md5($class), __FILE__);
        $registrar = $this->registrar($class, $plugin);

        $registrar->addHooks();

        // Empty keys are dropped; `plain_object` in `…\Tests\Api` resolves to `…\Tests\PlainObject`.
        $this->assertSame(
            ['plain_object' => __NAMESPACE__, 'missing_class' => __NAMESPACE__],
            $registrar->getObjectClasses()
        );
        $instances = $registrar->getOrder();
        $this->assertCount(1, $instances);
        $this->assertInstanceOf(PlainObject::class, $instances[0]);
        remove_all_filters($tag);
    }

    private function registrar(string $class, Plugin $plugin): ObjectRegistrarManager
    {
        $classes = ['' => __NAMESPACE__, 'plain_object' => __NAMESPACE__, 'missing_class' => __NAMESPACE__];

        return match ($class) {
            ColumnsRegistrar::class => new class ($plugin, $classes) extends ColumnsRegistrar {
                use RegistrarTestHelpers;

                protected function registerColumns(array $columns = []): array
                {
                    return $this->classes;
                }
            },
            CustomFieldsRegistrar::class => new class ($plugin, $classes) extends CustomFieldsRegistrar {
                use RegistrarTestHelpers;

                protected function registerCustomFields(array $custom_fields = []): array
                {
                    return $this->classes;
                }
            },
            PostTypesRegistrar::class => new class ($plugin, $classes) extends PostTypesRegistrar {
                use RegistrarTestHelpers;

                protected function registerPostTypes(array $post_types = []): array
                {
                    return $this->classes;
                }
            },
            default => new class ($plugin, $classes) extends TaxonomyRegistrar {
                use RegistrarTestHelpers;

                protected function registerTaxonomies(array $taxonomies = []): array
                {
                    return $this->classes;
                }
            },
        };
    }
}
