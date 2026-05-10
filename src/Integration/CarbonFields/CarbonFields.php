<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Integration\CarbonFields;

use Carbon_Fields\Carbon_Fields;
use Carbon_Fields\Container;
use Carbon_Fields\Container\Post_Meta_Container;
use Carbon_Fields\Container\Term_Meta_Container;
use Carbon_Fields\Exception\Incorrect_Syntax_Exception;
use TheFrosty\WpUtilities\Plugin\AbstractContainerProvider;
use function define;
use function defined;
use function wp_deregister_script;
use function wp_deregister_style;
use const Carbon_Fields\URL;
use const Carbon_Fields\VERSION;

/**
 * Class CarbonFields
 * @package TheFrosty\WpUtilities\Integration\CarbonFields
 */
abstract class CarbonFields extends AbstractContainerProvider implements FieldsInterface, TypeInterface
{

    use FieldsFactory;

    protected string $id = 'x402';

    public function addHooks(): void
    {
        // Hack Carbon Fields asset "location".
        $this->addAction('plugins_loaded', function (): void {
            if (!defined('Carbon_Fields\URL')) {
                define('Carbon_Fields\URL', $this->getPlugin()->getUrl('assets/vendor/htmlburger/carbon-fields'));
            }
        });
        $this->addAction('after_setup_theme', [Carbon_Fields::class, 'boot']);
        $this->addAction('carbon_fields_loaded', [$this, 'loaded']);
        $this->addAction('carbon_fields_register_fields', [$this, 'registerFields']);
    }

    /**
     * Return a cast container.
     * @param string $label
     * @return Post_Meta_Container
     * @throws Incorrect_Syntax_Exception
     */
    public function postMetaContainer(string $label): Post_Meta_Container
    {
        $container = Container::make(self::POST_META, $this->id, $label);
        if (!$container instanceof Post_Meta_Container) {
            throw new Incorrect_Syntax_Exception('');
        }

        return $container;
    }

    /**
     * Return a cast container.
     * @param string $label
     * @return Term_Meta_Container
     * @throws Incorrect_Syntax_Exception
     */
    public function termMetaContainer(string $label): Term_Meta_Container
    {
        $container = Container::make(self::TERM_META, $this->id, $label);
        if (!$container instanceof Term_Meta_Container) {
            throw new Incorrect_Syntax_Exception('');
        }

        return $container;
    }

    protected function loaded(): void
    {
        $this->addAction('admin_enqueue_scripts', [$this, 'reEnqueueScripts'], 12);
    }

    /**
     * Fix Carbon Fields Sidebar Manager scripts location.
     */
    protected function reEnqueueScripts(): void
    {
        // Deregister (remove) script & style.
        wp_deregister_style('carbon-sidebar-manager');
        wp_deregister_script('carbon-sidebar-manager');

        // Enqueue again without the "/core/Libraries/Sidebar_Manager/" assets prefix.
        wp_enqueue_style('carbon-sidebar-manager', URL . '/assets/css/app.css', [], VERSION);
        wp_enqueue_script('carbon-sidebar-manager', URL . '/assets/js/app.js', [], VERSION);
        wp_localize_script(
            'carbon-sidebar-manager',
            'crbSidebarl10n',
            [
                // phpcs:disable WordPress.WP.I18n.TextDomainMismatch
                'add_sidebar' => __('Add Sidebar', 'carbon-fields'),
                'enter_name_of_new_sidebar' => __('Please enter the name of the new sidebar:', 'carbon-fields'),
                'remove_sidebar_confirmation' => __('Are you sure you wish to remove this sidebar?', 'carbon-fields'),
                // phpcs:enable
            ]
        );
    }

    abstract protected function registerFields(): void;
}
