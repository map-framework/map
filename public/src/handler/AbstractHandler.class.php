<?php
namespace handler;

use store\Bucket;

/**
 * This file is part of the MAP-Framework.
 *
 * @author    Michael Piontkowski <mail@mpiontkowski.de>
 * @copyright Copyright 2016 Michael Piontkowski
 * @license   https://raw.githubusercontent.com/map-framework/map/master/LICENSE.txt Apache License 2.0
 */
abstract class AbstractHandler {

	/**
	 * @var Bucket
	 */
	protected $config;

	/**
	 * @param Bucket $config
	 */
	public function __construct(Bucket $config) {
		$this->config = $config;
	}

}
