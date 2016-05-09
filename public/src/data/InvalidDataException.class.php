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

	const EXPECT_MATCHING     = 'is matching';
	const EXPECT_NOT_MATCHING = 'isn\'t matching';

	/**
	 * @var string
	 */
	protected $expect = self::EXPECT_MATCHING;

	public function __construct(string $pattern, string ...$data) {
		parent::__construct('Expected that each item of data list '.$this->expect.' the pattern.');

		$this->setData('pattern', $pattern);
		$this->setData('dataList', $data);
	}

	public function setExpect(string $expect):InvalidDataException {
		$this->expect = $expect;
		return $this;
	}

}
