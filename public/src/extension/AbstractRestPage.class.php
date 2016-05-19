<?php
namespace extension;

use util\Bucket;

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
 *
 * TODO outsource into map-framework/addon-mode-rest (#33)
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
	 * check if user is authorized
	 */
	abstract public function access():bool;

	public function __construct(Bucket $config) {
		$this->config   = $config;
		$this->response = new Bucket();
	}

	final public function getResponse():Bucket {
		return clone $this->response;
	}

}
