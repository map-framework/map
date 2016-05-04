<?php
namespace data\net;

use data\AbstractData;
use data\InvalidDataException;

/**
 * This file is part of the MAP-Framework.
 *
 * @author    Michael Piontkowski <mail@mpiontkowski.de>
 * @copyright Copyright 2016 Michael Piontkowski
 * @license   https://raw.githubusercontent.com/map-framework/map/master/LICENSE.txt Apache License 2.0
 */
class MimeType extends AbstractData {

	const PATTERN_TYPE = '^(text|image|video|audio|application|multipart|message|model|x\-[A-Za-z0-9\-])\/[A-Za-z0-9\-]+$';

	/**
	 * @var string
	 */
	private $type;

	/**
	 * @throws InvalidDataException
	 */
	public function set(string $type) {
		$this->assertIsType($type);

		$this->type = $type;
	}

	public function get():string {
		return $this->type;
	}

	final public static function isType(string $type):bool {
		return self::isMatching(self::PATTERN_TYPE, $type);
	}

	/**
	 * @throws InvalidDataException
	 */
	final public static function assertIsType(string $type) {
		self::assertIsMatching(self::PATTERN_TYPE, $type);
	}

}
