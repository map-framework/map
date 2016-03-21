<?php

/**
 * simple autoload function
 *
 * @param  string $namespace
 * @return bool
 */
function mapAutoload($namespace) {
	$fileSuffix    = '.class.php';
	$directoryList = array(
			'public/src/',
			'private/src/'
	);

	$itemList = explode('\\', $namespace);

	foreach ($directoryList as $directory) {
		$path = '../'.$directory.implode('/', $itemList).$fileSuffix;
		if (file_exists($path)) {
			include_once $path;
			return true;
		}
	}
	return false;
}

# register spl autoload-function
spl_autoload_register('mapAutoload', true);