<?php

/**
 * This file is part of the MAP-Framework.
 *
 * @author    Michael Piontkowski <mail@mpiontkowski.de>
 * @copyright Copyright 2016 Michael Piontkowski
 * @license   https://raw.githubusercontent.com/map-framework/map/master/LICENSE.txt Apache License 2.0
 */
function mapAutoload(string $namespace):bool {
	$fileSuffix    = '.class.php';
	$directoryList = array(
			'public/src/',
			'private/src/'
	);
	$itemList      = explode('\\', $namespace);

	foreach ($directoryList as $directory) {
		$path = '../'.$directory.implode('/', $itemList).$fileSuffix;
		if (file_exists($path)) {
			include_once $path;
			return true;
		}
	}
	return false;
}

spl_autoload_register('mapAutoload', true);