<?php
namespace extension;

use store\Bucket;

/**
 * Implement the following methods. Returns HTTP-Status Code (HttpConst).
 * [GET]  /rest/person/nickname -> getNickname(request:MAPUrl):int|bool
 * [PUT]  /rest/person/age      -> putAge(request:MAPUrl):int|bool
 * [POST] /rest/person          -> postIndex(request:MAPUrl):int|bool
 *
 * @example (see above)
 */
abstract class AbstractRestPage {

	/**
	 * @var Bucket
	 */
	protected $config = null;

	/**
	 * @var Bucket
	 */
	protected $response = null;

	/**
	 * @param Bucket $config
	 */
	public function __construct(Bucket $config) {
		$this->config   = $config;
		$this->response = new Bucket();
	}

	/**
	 * check if user is entitled
	 *
	 * @return bool
	 */
	abstract public function access();

	/**
	 * @return Bucket
	 */
	final public function getResponse() {
		return clone $this->response;
	}

}
