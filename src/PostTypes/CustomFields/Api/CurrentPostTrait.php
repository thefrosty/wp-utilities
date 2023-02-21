<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\PostTypes\CustomFields\Api;

use RuntimeException;
use TheFrosty\WpUtilities\Plugin\HttpFoundationRequestInterface;
use function absint;
use function get_the_ID;
use function is_admin;
use function is_numeric;
use function sprintf;

/**
 * Class CurrentPostTrait
 * @package TheFrosty\WpUtilities\PostTypes\CustomFields\Api
 */
trait CurrentPostTrait
{

    /**
     * Get current post ID.
     * @return int|null
     * @throws RuntimeException
     */
    protected function getCurrentPostId(): ?int
    {
        if (!is_admin()) {
            return get_the_ID() ? get_the_ID() : null;
        }
        if (!$this instanceof HttpFoundationRequestInterface) {
            throw new RuntimeException(
                sprintf('Not an instance of %s', HttpFoundationRequestInterface::class)
            );
        }
        $query = $this->getRequest()->query;
        $request = $this->getRequest()->request;
        $post_id = $query->has('post') ?
            $query->get('post') : ($request->has('post_ID') ? $request->get('post_ID') : null);

        return is_numeric($post_id) ? absint($post_id) : null;
    }
}
