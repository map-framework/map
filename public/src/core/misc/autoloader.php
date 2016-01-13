<?php

/**
 * simple autoloader
 * 
 * @param string $namespace
 * @return bool
 */
function afterAutoload($namespace) {
	$directoryList = ['src/',	'../private'];
	$fileExtension = '.class.php';

	$itemList = explode('\\', $namespace);
	
	foreach ($directoryList as $directory) {
		$path = $directory.implode('/', $itemList).'.class.php';
		if (file_exists($path)) {
			return include_once $path;
			return true;
		}
	}
	return false;
}

# register spl autoloader
spl_autoload_register('afterAutoload', true);