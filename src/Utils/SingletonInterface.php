<?php

namespace TheFrosty\WP\Utils\Utils;

/**
 * Interface SingletonInterface
 *
 * @package TheFrosty\WP\Utils\Utils
 */
interface SingletonInterface {

	/**
	 * @return SingletonInterface
	 */
	public static function getInstance(): SingletonInterface;
}
