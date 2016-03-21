<?php
namespace store\data\math\number;

class BinaryNumber extends AbstractNumber {

	/**
	 * @return array[int:key] = digit:string
	 */
	final public function getDigitList() {
		return array('0', '1');
	}

}
