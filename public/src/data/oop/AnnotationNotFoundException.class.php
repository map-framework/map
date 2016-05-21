<?php
namespace data\oop;

use util\MAPException;

/**
 * This file is part of the MAP-Framework.
 *
 * @author    Michael Piontkowski <mail@mpiontkowski.de>
 * @copyright Copyright 2016 Michael Piontkowski
 * @license   https://raw.githubusercontent.com/map-framework/map/master/LICENSE.txt Apache License 2.0
 */
class AnnotationNotFoundException extends MAPException {

	public function __construct(Annotation ...$annotation) {
		parent::__construct('Annotations not found');

		foreach ($annotation as $a) {
			$this->addAnnotation($a);
		}
	}

	public function addAnnotation(Annotation $annotation):AnnotationNotFoundException {
		return $this->addData('annotationList', $annotation);
	}

}
