<?php declare(strict_types=1);

namespace TheFrosty\PhpUnit\WpUtilities\Framework\Mock;

use TheFrosty\WpUtilities\Plugin\AbstractHookProvider;

/**
 * Class HookProvider
 *
 * @package TheFrosty\PhpUnit\WpUtilities\Framework\Mock
 */
class HookProvider extends AbstractHookProvider
{

    /**
     * Add hooks.
     */
    public function addHooks()
    {
    }

    /**
     * Register hooks.
     *
     * @return bool
     */
    public function registerActions()
    {
        return $this->addAction('template_redirect', [$this, 'templateRedirect']);
    }

    /**
     * Register filters.
     *
     * @return bool
     */
    public function registerFilters()
    {
        return $this->addFilter('the_title', [$this, 'getTitle']);
    }

    /**
     * Empty method for the action.
     */
    protected function templateRedirect()
    {
    }

    /**
     * Empty method for the filter.
     *
     * @return string
     */
    protected function getTitle()
    {
        return 'Title';
    }
}
