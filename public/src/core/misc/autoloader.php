<?php

/**
 * simple autoloader
 * 
 * @param string $namespace
 * @throws RuntimeException if class not found
 */
function __autoload($namespace) {
	$directoryList = ['src/',	'../private'];
	$fileExtension = '.class.php';

	$itemList = explode('\\', $namespace);
	
	foreach ($directoryList as $directory) {
		$path = $directory.implode('/', $itemList).'.class.php';
		if (file_exists($path)) {
			return include_once $path;
		}
	}
	
	throw new RuntimeException('Class `'.$namespace.'` not found.');
}