<?php

/**
 * simple autoloader
 * @param  string $namespace
 * @return bool
 */
function afterAutoload($namespace) {
	# change directory if not usual installed
	$directoryList = array(
		'public/src/',
		'private/src/'
	);

	# binary folders on command line
	if (PHP_SAPI === 'cli') {
		$directoryList[] = 'public/bin/';
		$directoryList[] = 'private/bin/';
		$prefix = '';
	}
	else {
		$prefix = '../';
	}
	
	$suffix = '.class.php';
	$itemList = explode('\\', $namespace);
	
	foreach ($directoryList as $directory) {
		$path = $prefix.$directory.implode('/', $itemList).$suffix;
		if (file_exists($path)) {
			include_once $path;
			return true;
		}
	}
	return false;
}

# register spl autoloader
spl_autoload_register('afterAutoload', true);