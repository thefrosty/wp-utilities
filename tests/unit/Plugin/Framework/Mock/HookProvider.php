<?php declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\Plugin\Framework\Mock;

use TheFrosty\WpUtilities\Plugin\AbstractHookProvider;

/**
 * Class HookProvider
 *
 * @package TheFrosty\WpUtilities\Tests\Plugin\Framework\Mock
 */
class HookProvider extends AbstractHookProvider
{

    /**
     * Add hooks.
     */
    public function addHooks(): void
    {
    }

    /**
     * Register hooks.
     * @return bool
     */
    public function registerActions(): bool
    {
        return $this->addAction('template_redirect', [$this, 'templateRedirect']);
    }

    /**
     * Register filters.
     * @return bool
     */
    public function registerFilters(): bool
    {
        return $this->addFilter('the_title', [$this, 'getTitle']);
    }

    /**
     * Empty method for the action.
     */
    protected function templateRedirect(): void
    {
    }

    /**
     * Empty method for the filter.
     * @return string
     */
    protected function getTitle(): string
    {
        return 'Title';
    }
}
