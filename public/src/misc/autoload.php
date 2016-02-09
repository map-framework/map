<?php

/**
 * simple autoloader
 * @param  string $namespace
 * @return bool
 */
function mapAutoload($namespace) {
	# change directory if not usual installed
	$directoryList = array(
		'public/src/',
		'private/src/'
	);

	# binary folders on command line
	if (PHP_SAPI === 'cli') {
		$directoryList[] = 'public/bin/';
		$directoryList[] = 'private/bin/';
	}
	
	$suffix = '.class.php';
	$itemList = explode('\\', $namespace);
	
	foreach ($directoryList as $directory) {
		$path = constant('ROOT_DIR').$directory.implode('/', $itemList).$suffix;
		if (file_exists($path)) {
			include_once $path;
			return true;
		}
	}
	return false;
}

# set root dir
if (PHP_SAPI === 'cli') {
	define('ROOT_DIR', '');
}
else {
	define('ROOT_DIR', '../');
}

# register spl autoloader
spl_autoload_register('mapAutoload', true);