<?php
namespace data;

use util\MAPException;

/**
 * This file is part of the MAP-Framework.
 *
 * @author    Michael Piontkowski <mail@mpiontkowski.de>
 * @copyright Copyright 2016 Michael Piontkowski
 * @license   https://raw.githubusercontent.com/map-framework/map/master/LICENSE.txt Apache License 2.0
 */
class InvalidDataException extends MAPException {

	public function __construct(string $pattern, string ...$subject) {
		parent::__construct('Expected that each subject is matching at least one pattern.');

		$this->addPattern($pattern);
		foreach ($subject as $s) {
			$this->addSubject($s);
		}
	}

	public function addPattern(string $pattern):InvalidDataException {
		return $this->addData('patternList', $pattern);
	}

	public function addSubject(string $subject):InvalidDataException {
		return $this->addData('subjectList', $subject);
	}

}
