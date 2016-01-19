<?php
namespace store\data\net;

use Exception;

/**
 * @todo test me!
 */
class MAPUrl extends Url {

	const PATTERN_MODE	= '^[0-9A-Za-z_\-+]{1,32}$';
	const PATTERN_AREA 	= '^[0-9A-Za-z_\-+]{1,32}$';
	const PATTERN_PAGE	= '^[0-9A-Za-z]{1,32}$';
	const PATTERN_INPUT	= '^[0-9A-Za-z_\-+ÄÖÜßäöü;,]+$';

	private $mode 			= null;
	private $area 			= null;
	private $page 			= null;
	private $inputList 	= array();

	/**
	 * set mode
	 * @param  string $mode
	 * @throws Exception if mode invalid
	 * @return MAPUrl
	 */
	public function setMode($mode) {
		if (!self::match(self::PATTERN_MODE, $mode)) {
			throw new Exception('mode `'.$mode.'` is invalid');
		}
		$this->mode = $mode;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getMode() {
		return $this->mode;
	}

	/**
	 * set area
	 * @param  string $area
	 * @throws Exception if area invalid
	 * @return MAPUrl
	 */
	public function setArea($area) {
		if (!self::match(self::PATTERN_AREA, $area)) {
			throw new Exception('area `'.$area.'` is invalid');
		}
		$this->area = $area;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getArea() {
		return $this->area;
	}

	/**
	 * set page
	 * @param  string $page
	 * @throws Exception if page invalid
	 * @return MAPUrl
	 */
	public function setPage($page) {
		if (!self::match(self::PATTERN_PAGE, $page)) {
			throw new Exception('page `'.$page.'` is invalid');
		}
		$this->page = $page;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getPage() {
		return $this->page;
	}

	/**
	 * @param  $inputList string[]
	 * @throws Exception if input invalid
	 * @return MAPUrl
	 */
	public function setInputList($inputList) {
		$this->inputList = array();

		foreach ($inputList as $input) {
			$this->addInput($input);
		}
		return $this;
	}

	/**
	 * @param  $input string
	 * @throws Exception if input invalid
	 * @return MAPUrl
	 */
	public function addInput($input) {
		if (!self::match(self::PATTERN_INPUT, $input)) {
			throw new Exception('input `'.$input.'` is invalid');
		}
		$this->inputList[] = $input;
		return $this;
	}

	/**
	 * @return string[]
	 */
	public function getInputList() {
		return $this->inputList;
	}

	/**
	 * @see    Url::setPath()
	 * @throws Exceptions if anything is invalid
	 */
	public function setPath($path) {
		$itemList = explode('/', $path);

		# reset values
		$this->mode 	= null;
		$this->area 	= null;
		$this->page 	= null;
		$this->input 	= array();

		# assign
		foreach ($itemList as $item) {
			if (trim($item) === '') {
				continue;
			}
			elseif ($this->getMode() === null) {
				$this->setMode($item);
			}
			elseif ($this->getArea() === null) {
				$this->setArea($item);
			}
			elseif ($this->getPage() === null) {
				$this->setPage($item);
			}
			else {
				$this->addInput($item);
			}
		}
		return $this;
	}

	/**
	 * @see Url::getPath()
	 */
	public function getPath() {
		$itemList = array();
		
		# mode
		if ($this->getMode() !== null) {
			$itemList[] = $this->getMode();
		}

		# area
		if ($this->getArea() !== null) {
			$itemList[] = $this->getArea();
		}
		
		# page
		if ($this->getPage() !== null) {
			$itemList[] = $this->getPage();
		}

		# input
		$itemList = array_merge($itemList, $this->getInputList());

		return '/'.implode('/', $itemList);
	}

}