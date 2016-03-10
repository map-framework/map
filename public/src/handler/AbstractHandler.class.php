<?php
namespace handler;

use store\Bucket;

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
