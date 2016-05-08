<?php
namespace data\net;

use data\InvalidDataException;
use data\map\Area;
use data\map\Mode;
use data\norm\InvalidDataTypeException;
use util\Bucket;
use util\Logger;
use util\MAPException;

/**
 * This file is part of the MAP-Framework.
 *
 * @author    Michael Piontkowski <mail@mpiontkowski.de>
 * @copyright Copyright 2016 Michael Piontkowski
 * @license   https://raw.githubusercontent.com/map-framework/map/master/LICENSE.txt Apache License 2.0
 */
class MAPUrl extends Url {

	const PATTERN_PAGE  = '^[0-9A-Za-z]{0,32}$';
	const PATTERN_INPUT = '^[A-Za-z0-9\-_.!~*\'();%]+$';

	/**
	 * @var Bucket
	 */
	protected $config;

	/**
	 * @var Mode
	 */
	private $mode;

	/**
	 * @var Area
	 */
	private $area;

	/**
	 * @var string
	 */
	private $page;

	/**
	 * @var array
	 */
	private $inputList = array();

	public function __construct(string $url, Bucket $config) {
		$this->config = $config;
		parent::__construct($url);
	}

	/**
	 * @throws InvalidDataException
	 * @throws InvalidDataTypeException
	 * @throws MAPException
	 */
	public function setPath(string $path):MAPUrl {
		self::assertIsPath($path);

		$this->mode      = null;
		$this->area      = null;
		$this->page      = null;
		$this->inputList = array();

		$level = 0;
		foreach (explode('/', $path) as $item) {
			if ($item === '') {
				continue;
			}

			if ($level === 0) {
				$level++;
				try {
					$this->setMode($this->getTargetMode(new Mode($item)));
					continue;
				}
				catch (InvalidDataException $e) {
					Logger::debug(
							'The Mode-Name is invalid.',
							array(
									'path'    => $path,
									'mode'    => $item,
									'pattern' => Mode::PATTERN_NAME
							)
					);
				}
				catch (MAPException $e) {
					if ($e instanceof InvalidDataTypeException) {
						throw $e;
					}
					Logger::debug(
							'The Mode not exists.',
							array(
									'path' => $path,
									'mode' => $item
							)
					);
				}
			}

			if ($level === 1) {
				$level++;
				try {
					$this->setArea($this->getTargetArea(new Area($item)));
					continue;
				}
				catch (InvalidDataException $e) {
					Logger::debug(
							'The Area-Name is invalid.',
							array(
									'path'    => $path,
									'area'    => $item,
									'pattern' => Area::PATTERN_NAME
							)
					);
				}
				catch (MAPException $e) {
					if ($e instanceof InvalidDataTypeException) {
						throw $e;
					}
					Logger::debug(
							'The Area not exists.',
							array(
									'path' => $path,
									'area' => $item,
							)
					);
				}
			}

			if ($level === 2) {
				$level++;
				try {
					$this->setPage($this->getTargetPage($item));
					continue;
				}
				catch (InvalidDataException $e) {
					Logger::debug(
							'The Page-Name is invalid.',
							array(
									'path'    => $path,
									'area'    => $item,
									'pattern' => self::PATTERN_PAGE
							)
					);
				}
			}

			try {
				$this->addInput($item);
			}
			catch (InvalidDataException $e) {
				Logger::debug(
						'The Input-Name is invalid (Item ignored).',
						array(
								'path'    => $path,
								'input'   => $item,
								'pattern' => self::PATTERN_INPUT
						)
				);
			}
		}

		if (!$this->hasMode()) {
			$mode = $this->getDefaultMode();
			$mode->assertExists($this->config);
			$this->mode = $mode;
		}
		if (!$this->hasArea()) {
			$area = $this->getDefaultArea();
			$area->assertExists();
			$this->area = $area;
		}
		if (!$this->hasPage()) {
			$this->page = $this->getDefaultPage();
		}
		return $this;
	}

	public function getPath(string $path):string {
		if ($this->hasMode()) {
			$itemList[] = $this->getMode()->get();
		}
		if ($this->hasArea()) {
			$itemList[] = $this->getArea()->get();
		}
		if ($this->hasPage()) {
			$itemList[] = $this->getPage();
		}
		return '/'.implode('/', array_merge($itemList ?? array(), $this->getInputList()));
	}

	/**
	 * @throws MAPException
	 */
	public function setMode(Mode $mode):MAPUrl {
		$mode->assertExists($this->config);

		$this->mode = clone $mode;
		return $this;
	}

	public function getMode():Mode {
		return $this->mode;
	}

	/**
	 * @throws InvalidDataException
	 * @throws InvalidDataTypeException
	 */
	protected function getTargetMode(Mode $mode):Mode {
		if (!$this->config->isNull('alias', 'mode')) {
			$this->config->assertIsArray('alias', 'mode');

			$aliasList = $this->config->get('alias', 'mode');
			if (isset($aliasList[$mode->get()])) {
				return $this->getTargetMode(new Mode($aliasList[$mode->get()]));
			}
		}
		return $mode;
	}

	/**
	 * @throws InvalidDataException
	 */
	public function getDefaultMode():Mode {
		return new Mode($this->config->get('default', 'mode'));
	}

	public function hasMode():bool {
		return $this->mode !== null;
	}

	/**
	 * @throws MAPException
	 */
	public function setArea(Area $area):MAPUrl {
		$area->assertExists();

		$this->area = clone $area;
		return $this;
	}

	public function getArea():Area {
		return $this->area;
	}

	/**
	 * @throws InvalidDataException
	 * @throws InvalidDataTypeException
	 */
	protected function getTargetArea(Area $area):Area {
		if (!$this->config->isNull('alias', 'area')) {
			$this->config->assertIsArray('alias', 'area');

			$aliasList = $this->config->get('alias', 'area');
			if (isset($aliasList[$area->get()])) {
				return $this->getTargetArea(new Area($aliasList[$area->get()]));
			}
		}
		return $area;
	}

	/**
	 * @throws InvalidDataException
	 */
	public function getDefaultArea():Area {
		return new Area($this->config->get('default', 'area'));
	}

	public function hasArea():bool {
		return $this->area !== null;
	}

	/**
	 * @throws InvalidDataException
	 */
	public function setPage(string $page):MAPUrl {
		self::assertIsPage($page);

		$this->page = $page;
		return $this;
	}

	public function getPage():string {
		return $this->page;
	}

	/**
	 * @throws InvalidDataException
	 * @throws InvalidDataTypeException
	 */
	protected function getTargetPage(string $page):string {
		self::assertIsPage($page);

		if (!$this->config->isNull('alias', 'page')) {
			$this->config->assertIsArray('alias', 'page');

			$aliasList = $this->config->get('alias', 'page');
			if (isset($aliasList[$page])) {
				return $this->getTargetPage($aliasList[$page]);
			}
		}
		return $page;
	}

	/**
	 * @throws InvalidDataException
	 */
	public function getDefaultPage():string {
		self::assertIsPage($this->config->get('default', 'page'));
		return $this->config->get('default', 'page');
	}

	public function hasPage():bool {
		return $this->page !== null;
	}

	/**
	 * @throws InvalidDataException
	 */
	public function setInputList(array $inputList):MAPUrl {
		$this->inputList = array();

		foreach ($inputList as $input) {
			$this->addInput($input);
		}
		return $this;
	}

	/**
	 * @throws InvalidDataException
	 */
	public function addInput(string ...$input):MAPUrl {
		foreach ($input as $i) {
			self::assertIsInput($i);

			$this->inputList[] = $i;
		}
		return $this;
	}

	public function getInputList():array {
		return $this->inputList;
	}

	final public static function isPage(string $page):bool {
		return self::isMatching(self::PATTERN_PAGE, $page);
	}

	final public static function isInput(string $input):bool {
		return self::isMatching(self::PATTERN_INPUT, $input);
	}

	/**
	 * @throws InvalidDataException
	 */
	final public static function assertIsPage(string $page) {
		self::assertIsMatching(self::PATTERN_PAGE, $page);
	}

	/**
	 * @throws InvalidDataException
	 */
	final public static function assertIsInput(string $input) {
		self::assertIsMatching(self::PATTERN_INPUT, $input);
	}

}
