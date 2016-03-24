<?php
namespace handler\mode;

use exception\file\FileNotFoundException;
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
	public function handle():AbstractModeHandler {
		try {
			$file = $this->getFile();
		}
		catch (FileNotFoundException $e) {
			return $this->error(404);
		}

		echo $this->translate($file->getContents());
		return $this;
	}

}
