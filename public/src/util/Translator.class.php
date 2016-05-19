<?php
namespace util;

use data\common\InvalidDataTypeException;
use data\file\File;
use data\file\ForbiddenException;
use data\file\UnexpectedTypeException;
use data\net\MAPUrl;

/**
 * This file is part of the MAP-Framework.
 *
 * @author    Michael Piontkowski <mail@mpiontkowski.de>
 * @copyright Copyright 2016 Michael Piontkowski
 * @license   https://raw.githubusercontent.com/map-framework/map/master/LICENSE.txt Apache License 2.0
 */
class Translator {

	const VAR_MODE = '{MODE}';
	const VAR_AREA = '{AREA}';
	const VAR_PAGE = '{PAGE}';
	const VAR_LANG = '{LANG}';

	const FILE_AUTO_AREA = 'private/src/area/{AREA}/text/{LANG}/area.ini';
	const FILE_AUTO_PAGE = 'private/src/area/{AREA}/text/{LANG}/page/{PAGE}.ini';

	const FORMAT_PLACEHOLDER = '{%s#%s}';

	/**
	 * @var Bucket
	 */
	protected $texts;

	/**
	 * @throws InvalidDataTypeException
	 * @throws UnexpectedTypeException
	 * @throws ForbiddenException
	 */
	public function __construct(Bucket $config = null, MAPUrl $request = null) {
		$this->texts = new Bucket();

		if ($config !== null && $request !== null) {
			$this->updateTexts($config, $request);
		}
	}

	public function getTexts():Bucket {
		return $this->texts;
	}

	/**
	 * @throws InvalidDataTypeException
	 * @throws UnexpectedTypeException
	 * @throws ForbiddenException
	 */
	public function updateTexts(Bucket $config, MAPUrl $request):Translator {
		$config->assertIsString('multiLang', 'language');

		# fix load list
		if (!$config->isNull('multiLang', 'loadList')) {
			$config->assertIsArray('multiLang', 'loadList');

			$loadPathList = $config->get('multiLang', 'loadList');
		}

		# auto area file
		if (!$config->isNull('multiLang', 'autoAreaFile')) {
			$config->assertIsBool('multiLang', 'autoAreaFile');

			if ($config->isTrue('multiLang', 'autoAreaFile')) {
				$loadPathList[] = self::FILE_AUTO_AREA;
			}
		}

		# auto page file
		if (!$config->isNull('multiLang', 'autoPageFile')) {
			$config->assertIsBool('multiLang', 'autoPageFile');

			if ($config->isTrue('multiLang', 'autoPageFile')) {
				$loadPathList[] = self::FILE_AUTO_PAGE;
			}
		}

		if (count($loadPathList ?? array())) {
			foreach ($loadPathList as $loadPath) {
				# replace variables
				$loadPath = str_replace(self::VAR_MODE, $request->getMode(), $loadPath);
				$loadPath = str_replace(self::VAR_AREA, $request->getArea(), $loadPath);
				$loadPath = str_replace(self::VAR_PAGE, $request->getPage(), $loadPath);
				$loadPath = str_replace(self::VAR_LANG, $config->get('multiLang', 'language'), $loadPath);
				$loadFile = new File($loadPath);

				# apply file
				if ($loadFile->exists()) {
					$loadFile->assertIsFile();
					$loadFile->assertIsReadable();

					$this->texts->applyIni($loadFile);
				}
				else {
					Logger::debug(
							'Lang-File not found.',
							array(
									'file' => $loadFile
							)
					);
				}
			}
		}
		else {
			Logger::debug(
					'MultiLang Load-List is empty.',
					array(
							'multiLang[language]'     => $config->get('multiLang', 'language'),
							'multiLang[autoAreaFile]' => $config->get('multiLang', 'autoAreaFile'),
							'multiLang[autoPageFile]' => $config->get('multiLang', 'autoPageFile')
					)
			);
		}
		return $this;
	}

	public function translate(string $text):string {
		foreach ($this->texts->toArray() as $group => $groupKeyList) {
			foreach ($groupKeyList as $key => $value) {
				$text = str_replace(
						sprintf(
								self::FORMAT_PLACEHOLDER,
								$group,
								$key
						),
						$value,
						$text
				);
			}
		}
		return $text;
	}

	/**
	 * Language is changed at the next request.
	 */
	public static function setLanguage(string $language, Bucket $config = null) {
		$_SESSION['config'] = (new Bucket($_SESSION['config'] ?? array()))
				->set('multiLang', 'language', $language)
				->toArray();
		if ($config !== null) {
			$config->set('multiLang', 'language', $language);
		}
	}

}
