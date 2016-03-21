<?php
namespace store\data\math\number;

use RuntimeException;
use store\data\AbstractData;

abstract class AbstractNumber extends AbstractData {

	/**
	 * @return array[int:key] = digit:string
	 */
	abstract public function getDigitList();

	/**
	 * @param  mixed $data
	 * @return AbstractNumber
	 */
	public function set($data) {
		$digitList    = str_split(strtoupper($data));
		$numberString = '';

		foreach ($digitList as $digit) {
			if ($digit === $this->getLowestDigit() && strlen($numberString) === 0) {
				continue;
			}
			if (!in_array($digit, $this->getDigitList())) {
				throw new RuntimeException('Invalid number `'.$data.'`');
			}
			$numberString .= $digit;
		}

		if (strlen($numberString) === 0) {
			$numberString = $this->getLowestDigit();
		}
		return parent::set($numberString);
	}

	/**
	 * @return int
	 */
	final public function getBase() {
		return count($this->getDigitList());
	}

	/**
	 * get the lowest digit
	 *
	 * @return string
	 */
	final public function getLowestDigit() {
		return $this->getDigitList()[0];
	}

	/**
	 * get the greatest digit
	 *
	 * @return string
	 */
	final public function getGreatestDigit() {
		return $this->getDigitList()[$this->getBase() - 1];
	}

}
