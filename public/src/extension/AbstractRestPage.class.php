<?php
namespace extension;

use store\Bucket;

/**
 * This file is part of the MAP-Framework.
 *
 * @author    Michael Piontkowski <mail@mpiontkowski.de>
 * @copyright Copyright 2016 Michael Piontkowski
 * @license   https://raw.githubusercontent.com/map-framework/map/master/LICENSE.txt Apache License 2.0
 *
 * [GET]  /rest/person/nickname -> +AbstractRestPage::getNickname(request:MAPUrl):int
 * [PUT]  /rest/person/age      -> +putAge(request:MAPUrl):int
 * [POST] /rest/person          -> +postIndex(request:MAPUrl):int
 *
 * @example   Implement methods like examples. Return HTTP-Status Codes. (see above)
 * @see       HttpConst
 */
abstract class AbstractRestPage {

	/**
	 * @var Bucket
	 */
	protected $config;

	/**
	 * @var Bucket
	 */
	protected $response;

	/**
	 * @param Bucket $config
	 */
	public function __construct(Bucket $config) {
		$this->config   = $config;
		$this->response = new Bucket();
	}

	/**
	 * check if user is authorized
	 *
	 * @return bool
	 */
	abstract public function access():bool;

	/**
	 * @return Bucket
	 */
	final public function getResponse():Bucket {
		return clone $this->response;
	}

}
