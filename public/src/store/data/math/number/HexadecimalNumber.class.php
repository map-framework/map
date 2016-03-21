<?php
namespace store\data\math\number;

class HexadecimalNumber extends AbstractNumber {

	/**
	 * @return array[int:key] = digit:string
	 */
	final public function getDigitList() {
		return array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'A', 'B', 'C', 'D', 'E', 'F');
	}

}
