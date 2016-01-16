<?php

/**
 * simple autoloader
 * 
 * @param  string $namespace
 * @return bool
 */
function afterAutoload($namespace) {
	# change directory if not usual installed
	$directoryList = array(
		'public/',
		'private/'
	);

	# makes possible to start unit tests from rootDir
	if (substr(getcwd(), strlen(getcwd()) - 6) === 'public') {
		$prefix = '../';
	}
	else {
		$prefix = '';
	}
	
	$suffix = '.class.php';
	$itemList = explode('\\', $namespace);
	
	foreach ($directoryList as $directory) {
		$path = $prefix.$directory.implode('/', $itemList).$suffix;
		if (file_exists($path)) {
			return include_once $path;
			return true;
		}
	}
	return false;
}

# register spl autoloader
spl_autoload_register('afterAutoload', true);