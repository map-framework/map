<?php
/**
 * you can use this functions in xsl context
 */

/**
 * get text
 * @param  $group
 * @param  $key
 * @return string
 */
function text($group, $key) {
	$textBucket = unserialize(constant('TEXTS'));

	$text = $textBucket->get($group, $key);
	if ($text !== null) {
		return $text;
	}
	return '';
}