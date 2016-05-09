<?php
namespace data\net;

use util\MAPException;

/**
 * This file is part of the MAP-Framework.
 *
 * @author    Michael Piontkowski <mail@mpiontkowski.de>
 * @copyright Copyright 2016 Michael Piontkowski
 * @license   https://raw.githubusercontent.com/map-framework/map/master/LICENSE.txt Apache License 2.0
 */
class ParseException extends MAPException {

	public function __construct(string $url) {
		parent::__construct('The URL is malformed.');

		$this->setData('url', $url);
	}

}
