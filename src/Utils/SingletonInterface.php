<?php

namespace TheFrosty\WP\Utils;

/**
 * Interface SingletonInterface
 *
 * @package TheFrosty\WP\Utils
 */
interface SingletonInterface {

	public static function getInstance(): SingletonInterface;
}
