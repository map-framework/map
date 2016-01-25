<?php
namespace store\data\net;

use Exception;
use store\Bucket;

class MAPUrl extends Url {

	const PATTERN_MODE	= '^[0-9A-Za-z_\-+]{1,32}$';
	const PATTERN_AREA 	= '^[0-9A-Za-z_\-+]{1,32}$';
	const PATTERN_PAGE	= '^[0-9A-Za-z]{1,32}$';
	const PATTERN_INPUT	= '^[0-9A-Za-z_\-+ÄÖÜßäöü;,]+$';

	private $mode 			= null;
	private $area 			= null;
	private $page 			= null;
	private $inputList 	= array();

	private $bucket 		= null;

	/**
	 * @param string $url
	 * @param Bucket $validate
	 * @todo  TEST ME!
	 */
	public function __construct($url, Bucket $validate = null) {
		$this->bucket = $validate;
		parent::__construct($url);
	}

	/**
	 * set mode
	 * @param  string $mode
	 * @throws Exception if mode invalid
	 * @return bool
	 * @todo   TEST ME!
	 */
	public function setMode($mode) {
		if ($mode !== null && !$this->isMode($mode)) {
			return false;
		}
		$this->mode = $mode;
		return true;
	}

	/**
	 * @param  string $mode
	 * @throws Exception if mode invalid
	 * @return bool
	 * @todo   TEST ME!
	 */
	public function isMode($mode) {
		if (!self::match(self::PATTERN_MODE, $mode)) {
			throw new Exception('mode `'.$mode.'` is invalid');
		}

		# no validation
		if ($this->bucket === null) {
			return true;
		}

		$modeData = $this->bucket->get('mode', $mode);
		return isset($modeData, $modeData['type'], $modeData['handler']);
	}

	/**
	 * @return string
	 * @todo   TEST ME!
	 */
	public function getMode() {
		if ($this->mode !== null || $this->bucket === null) {
			return $this->mode;
		}
		return $this->bucket->get('default', 'mode');
	}

	/**
	 * set area
	 * @param  string $area
	 * @throws Exception if area invalid
	 * @return false
	 * @todo   TEST ME!
	 */
	public function setArea($area) {
		if ($area !== null && !$this->isArea($area)) {
			return false;
		}
		$this->area = $area;
		return true;
	}

	/**
	 * @param  string $area
	 * @throws Exception if area invalid
	 * @return bool
	 * @todo   TEST ME!
	 */
	public function isArea($area) {
		if (!self::match(self::PATTERN_AREA, $area)) {
			throw new Exception('area `'.$area.'` is invalid');
		}

		# no validation
		if ($this->bucket === null) {
			return true;
		}

		$areaDir = new File('private/src/area/'.$area.'/');
		return $areaDir->isDir();
	}

	/**
	 * @return string
	 * @todo   TEST ME!
	 */
	public function getArea() {
		if ($this->area !== null || $this->bucket === null) {
			return $this->area;
		}
		return $this->bucket->get('default', 'area');
	}

	/**
	 * set page
	 * @param  string $page
	 * @throws Exception if page invalid
	 * @return MAPUrl
	 * @todo   rewrite
	 */
	public function setPage($page) {
		if ($page !== null && !self::match(self::PATTERN_PAGE, $page)) {
			throw new Exception('page `'.$page.'` is invalid');
		}
		$this->page = $page;
		return $this;
	}

	/**
	 * @return string
	 * @todo   rewrite
	 */
	public function getPage() {
		return $this->page;
	}

	/**
	 * @param  $inputList string[]
	 * @throws Exception if inputList isn't an array
	 * @throws Exception if input invalid
	 * @return MAPUrl
	 * @todo   rewrite
	 */
	public function setInputList($inputList) {
		$this->inputList = array();

		if (!is_array($inputList)) {
			throw new Exception('inputList isn\'t an array');
		}

		foreach ($inputList as $input) {
			$this->addInput($input);
		}
		return $this;
	}

	/**
	 * @param  $input string
	 * @throws Exception if input invalid
	 * @return MAPUrl
	 * @todo   rewrite
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
	 * @todo   rewrite
	 */
	public function getInputList() {
		return $this->inputList;
	}

	/**
	 * @see    Url::setPath()
	 * @throws Exceptions if anything is invalid
	 * @todo   rewrite
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
	 * @todo   rewrite
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