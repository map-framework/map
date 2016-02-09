<?php
namespace store\data\net;

use store\Bucket;
use store\data\File;

class MAPUrl extends Url {

	const PATTERN_MODE	= '^[0-9A-Za-z_\-+]{1,32}$';
	const PATTERN_AREA 	= '^[0-9A-Za-z_\-+]{1,32}$';
	const PATTERN_PAGE	= '^[0-9A-Za-z]{1,32}$';
	const PATTERN_INPUT	= '^[0-9A-Za-z_\-+ÄÖÜßäöü;,]+$';

	private $mode 			= null;
	private $area 			= null;
	private $page 			= null;
	private $inputList 	= array();

	private $config 		= null;

	/**
	 * @param string $url
	 * @param Bucket $config validate
	 */
	public function __construct($url = null, Bucket $config = null) {
		$this->config = $config;
		parent::__construct($url);
	}

	/**
	 * @param  string $mode
	 * @return bool
	 */
	public function isMode($mode) {
		if (!self::match(self::PATTERN_MODE, $mode)) {
			return false;
		}
		if ($this->config === null) {
			return true;
		}
		$modeData = $this->config->get('mode', $mode);
		return isset($modeData, $modeData['type'], $modeData['handler']);
	}

	/**
	 * @param  string $area
	 * @return bool
	 */
	public function isArea($area) {
		if (!self::match(self::PATTERN_AREA, $area)) {
			return false;
		}
		if ($this->config === null) {
			return true;
		}
		$areaDir = new File('private/src/area/'.$area.'/');
		return $areaDir->isDir();
	}

	/**
	 * @param  string $page
	 * @return bool
	 */
	public function isPage($page) {
		return self::match(self::PATTERN_PAGE, $page);
	}

	/**
	 * @param  string $input
	 * @return bool
	 */
	public function isInput($input) {
		return self::match(self::PATTERN_INPUT, $input);
	}

	/**
	 * @param  string $mode
	 * @return bool
	 */
	public function setMode($mode) {
		if ($mode !== null && !$this->isMode($mode)) {
			return false;
		}
		$this->mode = $mode;
		return true;
	}

	/**
	 * @param  string $area
	 * @return bool
	 */
	public function setArea($area) {
		if ($area !== null && !$this->isArea($area)) {
			return false;
		}
		$this->area = $area;
		return true;
	}

	/**
	 * @param  string $page
	 * @return bool
	 */
	public function setPage($page) {
		if ($page !== null && !$this->isPage($page)) {
			return false;
		}
		$this->page = $page;
		return true;
	}

	/**
	 * @param  $inputList string[]
	 * @return bool
	 */
	public function setInputList($inputList) {
		$this->inputList = array();
		foreach ($inputList as $input) {
			$this->addInput($input);
		}
		return true;
	}

	/**
	 * @param  $input string
	 * @return bool
	 */
	public function addInput($input) {
		if (!$this->isInput($input)) {
			return false;
		}
		$this->inputList[] = $input;
		return true;
	}

	/**
	 * @return string
	 */
	public function getMode() {
		if ($this->mode !== null || $this->config === null) {
			return $this->mode;
		}
		return $this->config->get('default', 'mode');
	}

	/**
	 * @return string
	 */
	public function getArea() {
		if ($this->area !== null || $this->config === null) {
			return $this->area;
		}
		return $this->config->get('default', 'area');
	}

	/**
	 * @return string
	 */
	public function getPage() {
		if ($this->page !== null || $this->config === null) {
			return $this->page;
		}
		return $this->config->get('default', 'page');
	}

	/**
	 * @return string[]
	 */
	public function getInputList() {
		return $this->inputList;
	}

	/**
	 * @see    Url::setPath()
	 * @param  string $path
	 * @return MAPUrl
	 */
	public function setPath($path) {
		$itemList = explode('/', $path);

		# reset values
		$this->mode 	= null;
		$this->area 	= null;
		$this->page 	= null;
		$this->input 	= array();

		$level = 0;

		# assign
		foreach ($itemList as $item) {
			$item = trim($item);
			if ($item === '') {
				continue;
			}

			# level 0 = mode
			if ($level === 0) {
				$level++;
				if ($this->setMode($item)) {
					continue;
				}
			}

			# level 1 = area
			if ($level === 1) {
				$level++;
				if ($this->setArea($item)) {
					continue;
				}
			}

			# level 2 = page
			if ($level === 2) {
				$level++;
				if ($this->setPage($item)) {
					continue;
				}
			}

			# level 3+ = input item
			$this->addInput($item);
		}
		return $this;
	}

	/**
	 * @see Url::getPath()
	 */
	public function getPath() {
		$itemList = array();
		
		if ($this->mode !== null) {
			$itemList[] = $this->mode;
		}

		if ($this->area !== null) {
			$itemList[] = $this->area;
		}
		
		if ($this->page !== null) {
			$itemList[] = $this->page;
		}

		$itemList = array_merge($itemList, $this->getInputList());
		return '/'.implode('/', $itemList);
	}

}