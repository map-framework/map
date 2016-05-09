<?php
namespace data\peer\http;

use data\AbstractEnum;

/**
 * This file is part of the MAP-Framework.
 *
 * @author    Michael Piontkowski <mail@mpiontkowski.de>
 * @copyright Copyright 2016 Michael Piontkowski
 * @license   https://raw.githubusercontent.com/map-framework/map/master/LICENSE.txt Apache License 2.0
 */
class MethodEnum extends AbstractEnum {

	const CONNECT = 'CONNECT';
	const DELETE  = 'DELETE';
	const GET     = 'GET';
	const HEAD    = 'HEAD';
	const OPTIONS = 'OPTIONS';
	const POST    = 'POST';
	const PUT     = 'PUT';
	const TRACE   = 'TRACE';

}
