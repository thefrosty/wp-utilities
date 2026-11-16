<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\WpAdmin;

use TheFrosty\WpUtilities\Plugin\AbstractHookProvider;
use YahnisElsts\PluginUpdateChecker\v6\PucFactory;
use function untrailingslashit;

/**
 * Class GitHubUpdateChecker
 * @package TheFrosty\WpUtilities\WpAdmin
 * @see \TheFrosty\WpUtilities\Tests\WpAdmin\GitHubUpdateCheckerTest
 */
class GitHubUpdateChecker extends AbstractHookProvider
{

    /**
     * GitHubUpdateChecker constructor.
     * @param string $repository
     * @param string|null $token
     */
    public function __construct(
        private readonly string $repository,
        private readonly ?string $token = null
    ) {
    }

    /**
     * Add class hooks.
     */
    public function addHooks(): void
    {
        $updateChecker = PucFactory::buildUpdateChecker(
            $this->buildUrl(),
            $this->getPlugin()->getFile(),
            $this->getPlugin()->getSlug()
        );

        if ($this->token) {
            $updateChecker->setAuthentication($this->token);
        }

        /** @var \YahnisElsts\PluginUpdateChecker\v6p0\Vcs\GitLabApi $api */
        $api = $updateChecker->getVcsApi();
        $api->enableReleaseAssets();
    }

    protected function buildUrl(): string
    {
        return esc_url(sprintf('https://github.com/%s/', untrailingslashit($this->repository)));
    }
}
