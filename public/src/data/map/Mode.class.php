<?php
namespace data\map;

use data\AbstractData;
use data\InvalidDataException;
use data\net\MimeType;
use exception\MAPException;
use handler\AbstractHandler;
use util\Bucket;
use util\Logger;

/**
 * This file is part of the MAP-Framework.
 *
 * @author    Michael Piontkowski <mail@mpiontkowski.de>
 * @copyright Copyright 2016 Michael Piontkowski
 * @license   https://raw.githubusercontent.com/map-framework/map/master/LICENSE.txt Apache License 2.0
 */
class Mode extends AbstractData {

	const PATTERN_NAME = '^[0-9A-Za-z_\-+]{1,32}$';

	/**
	 * @var array
	 */
	protected $settings;

	/**
	 * @var string
	 */
	private $name;

	public function __construct(string $name, Bucket $config) {
		parent::__construct($name);
		$this->settings = $config->get('mode', $name, array());
	}

	/**
	 * @throws InvalidDataException
	 */
	public function set(string $name) {
		self::assertIsName($name);

		$this->name = $name;
	}

	public function get():string {
		return $this->name;
	}

	final public function getSetting(string $key) {
		return $this->settings[$key] ?? null;
	}

	final public function getType():MimeType {
		return new MimeType($this->getSetting('type'));
	}

	final public function getHandler():string {
		return (string) $this->getSetting('handler');
	}

	final public function exists():bool {
		$type    = $this->getSetting('type');
		$handler = $this->getSetting('handler');

		if ($type === null) {
			$reason = 'missing type';
		}
		elseif ($handler === null) {
			$reason = 'missing handler';
		}
		elseif (!MimeType::isType($type)) {
			$reason = 'invalid type';
		}
		elseif (class_exists($handler)) {
			$reason = 'handler-class not found';
		}
		else {
			$handlerObject = new $handler();
			if ($handlerObject instanceof AbstractHandler) {
				return true;
			}
			$reason = 'handler-class not instance of '.AbstractHandler::class;
		}

		Logger::debug(
				'Mode not exists',
				array(
						'Mode'    => $this->get(),
						'Reason'  => $reason,
						'Type'    => $type,
						'Handler' => $handler
				)
		);
		return false;
	}

	/**
	 * @throws MAPException
	 */
	final public function assertExists() {
		if (!$this->exists()) {
			throw (new MAPException('Expected existing mode.'))
					->setData('mode', $this)
					->setData('settings', $this->settings);
		}
	}

	final public static function isName(string $name):bool {
		return self::isMatching(self::PATTERN_NAME, $name);
	}

	/**
	 * @throws InvalidDataException
	 */
	final public static function assertIsName(string $name) {
		self::assertIsMatching(self::PATTERN_NAME, $name);
	}

}
