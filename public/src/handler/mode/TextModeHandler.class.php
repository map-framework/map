<?php
namespace handler\mode;

use data\file\NotFoundException;
use exception\InvalidValueException;

/**
 * This file is part of the MAP-Framework.
 *
 * @author    Michael Piontkowski <mail@mpiontkowski.de>
 * @copyright Copyright 2016 Michael Piontkowski
 * @license   https://raw.githubusercontent.com/map-framework/map/master/LICENSE.txt Apache License 2.0
 */
class TextModeHandler extends AbstractModeHandler {

	/**
	 * @throws InvalidValueException
	 * @return AbstractModeHandler this
	 */
	public function handle() {
		try {
			$file = $this->getFile();
		}
		catch (NotFoundException $e) {
			return $this->error(404);
		}

		echo $this->translate($file->getContents());
	}

}
