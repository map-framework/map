<?php
namespace data\map;

use data\AbstractData;
use data\common\InvalidDataTypeException;
use data\InvalidDataException;
use data\net\MimeType;
use data\oop\ClassNotFoundException;
use data\oop\ClassObject;
use data\oop\InstanceException;
use handler\mode\AbstractModeHandler;
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
class Mode extends AbstractData {

	const FORMAT_CONFIG_GROUP = 'mode-%s';

	const PATTERN_NAME = '^[0-9A-Za-z_\-+]{1,32}$';

	/**
	 * @var string
	 */
	private $name;

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

	public function getConfigGroup():string {
		return sprintf(
				self::FORMAT_CONFIG_GROUP,
				$this->get()
		);
	}

	/**
	 * @throws InvalidDataException
	 * @throws InvalidDataTypeException
	 */
	final public function getType(Bucket $config):MimeType {
		$group = $this->getConfigGroup();
		$config->assertIsString($group, 'type');
		return new MimeType($config->get($group, 'type'));
	}

	/**
	 * @throws InvalidDataException
	 * @throws InvalidDataTypeException
	 */
	final public function getHandler(Bucket $config):ClassObject {
		$group = $this->getConfigGroup();
		$config->assertIsString($group, 'handler');
		return new ClassObject($config->get($group, 'handler'));
	}

	final public function exists(Bucket $config):bool {
		try {
			$type = $this->getType($config);
		}
		catch (InvalidDataTypeException $e) {
			$reason = 'type is not defined';
		}
		catch (InvalidDataException $e) {
			$reason = 'type is not a valid mime type';
		}

		if (!isset($reason)) {
			try {
				$handler = $this->getHandler($config);
				$handler->assertExists();
				$handler->assertIsChildOf(new ClassObject(AbstractModeHandler::class));
				$handler->assertIsNotAbstract();
			}
			catch (InvalidDataTypeException $e) {
				$reason = 'handler is not defined';
			}
			catch (InvalidDataException $e) {
				$reason = 'handler namespace is invalid';
			}
			catch (ClassNotFoundException $e) {
				$reason = 'handler not exists';
			}
			catch (InstanceException $e) {
				$reason = 'handler is not instance of '.AbstractModeHandler::class;
			}
			catch (MAPException $e) {
				$reason = 'handler is abstract';
			}
		}

		if (isset($reason)) {
			Logger::debug(
					'Mode not exists',
					array(
							'mode'    => $this->get(),
							'reason'  => $reason,
							'type'    => $type ?? null,
							'handler' => $handler ?? null
					)
			);
			return false;
		}
		return true;
	}

	/**
	 * @throws MAPException
	 */
	final public function assertExists(Bucket $config) {
		if (!$this->exists($config)) {
			throw (new MAPException('Expected existing mode.'))
					->setData('mode', $this);
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
