<?php
namespace handler\exception;

use exception\MAPException;
use store\data\File;
use store\Logger;
use Throwable;
use xml\Tree;
use xml\XSLProcessor;
use Web;

/**
 * This file is part of the MAP-Framework.
 *
 * @author    Michael Piontkowski <mail@mpiontkowski.de>
 * @copyright Copyright 2016 Michael Piontkowski
 * @license   https://raw.githubusercontent.com/map-framework/map/master/LICENSE.txt Apache License 2.0
 */
final class MAPExceptionHandler implements ExceptionHandler {

	const PATH_STYLESHEET = 'public/src/misc/xsl/mapException.xsl';

	public static function handle(Throwable $exception):bool {
		if (!($exception instanceof MAPException)) {
			return false;
		}

		$tree = new Tree('exception');
		$exception->toNode($tree->getRootNode());
		Logger::error('Uncaught MAPException (see: `'.Logger::storeTree($tree, '.xml').'`)');

		if (Web::isDev()) {
			echo (new XSLProcessor())
					->setStyleSheetFile(new File(self::PATH_STYLESHEET))
					->setDocumentDoc($tree->toDomDoc())
					->transform();
			return true;
		}
		elseif (Web::isProd()) {
			return true;
		}
		return false;
	}

}
