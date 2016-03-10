<?php
namespace extension;

use store\Bucket;
use store\data\net\MAPUrl;

abstract class AbstractRestPage {

	/**
	 * @var Bucket
	 */
	protected $config = null;

	/**
	 * @param Bucket $config
	 */
	public function __construct(Bucket $config) {
		$this->config = $config;
	}

	/**
	 * check if user is entitled
	 *
	 * @return bool
	 */
	abstract public function access();

	/**
	 * default method
	 *
	 * @param  MAPUrl $request
	 * @return bool
	 */
	public function index(MAPUrl $request) {
		return false;
	}

}
