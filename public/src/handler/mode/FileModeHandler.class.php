<?php
namespace handler\mode;

use data\file\File;
use data\peer\http\StatusEnum;
use util\Logger;

/**
 * This file is part of the MAP-Framework.
 *
 * @author    Michael Piontkowski <mail@mpiontkowski.de>
 * @copyright Copyright 2016 Michael Piontkowski
 * @license   https://raw.githubusercontent.com/map-framework/map/master/LICENSE.txt Apache License 2.0
 */
class FileModeHandler extends AbstractModeHandler {

	public function handle() {
		$mode = $this->request->getMode();
		$mode->assertHasSetting($this->config, 'folder');
		$mode->assertHasSetting($this->config, 'extension');

		$areaFile = $this->getTargetFile($this->request->getArea()->getDir());
		if ($areaFile->isFile()) {
			$this->outputFile($areaFile);
			return;
		}

		$considerCommon = $mode->getSetting($this->config, 'considerCommon');
		if ($considerCommon === true) {
			$commonFile = $this->getTargetFile(new File('private/src/common/app'));

			if ($commonFile->exists()) {
				$this->outputFile($commonFile);
				return;
			}
		}

		Logger::debug(
				'HTTP-Status Code 404 (File not found)',
				array(
						'areaFile'       => $areaFile,
						'considerCommon' => $considerCommon === true,
						'commonFile'     => $commonFile ?? null
				)
		);
		$this->outputFailurePage(new StatusEnum(StatusEnum::NOT_FOUND));
	}

	protected function getTargetFile(File $file):File {
		$mode         = $this->request->getMode();
		$pathItemList = array_merge(
				array($this->request->getPage()),
				$this->request->getInputList()
		);

		$pathItemList[count($pathItemList) - 1] .= $mode->getSetting($this->config, 'extension');

		$file->attach('app');
		$file->attach($mode->getSetting($this->config, 'folder'));

		foreach ($pathItemList as $pathItem) {
			$file->attach($pathItem);
		}
		return $file;
	}

	protected function outputFile(File $file) {
		$this->setContentType($this->request->getMode()->getType($this->config));
		$this->setResponseStatus(new StatusEnum(StatusEnum::OK));
		$file->output();
	}

}
