<?php
namespace extension;

use handler\mode\SiteModeHandler;
use store\Bucket;
use xml\Node;
use xml\Tree;

/**
 * This file is part of the MAP-Framework.
 *
 * @author    Michael Piontkowski <mail@mpiontkowski.de>
 * @copyright Copyright 2016 Michael Piontkowski
 * @license   https://raw.githubusercontent.com/map-framework/map/master/LICENSE.txt Apache License 2.0
 */
abstract class AbstractSitePage {

	/**
	 * The request was initialized.
	 */
	const STATUS_INIT = 'INIT';

	/**
	 * This status is returned if an already rejected request is reloading from session.
	 */
	const STATUS_RESTORED = 'RESTORED';

	/**
	 * The request was rejected (failed).
	 */
	const STATUS_REJECTED = 'REJECTED';

	/**
	 * The request was accepted (success).
	 */
	const STATUS_ACCEPTED = 'ACCEPTED';

	/**
	 * @example Double-Click
	 * 1. request: formId=abc = STATUS_ACCEPTED
	 * 2. request: formId=abc = STATUS_REPEATED
	 */
	const STATUS_REPEATED = 'REPEATED';

	/**
	 * @var Bucket
	 */
	protected $config = null;

	/**
	 * @var array
	 */
	protected $request = array();

	/**
	 * @var Tree
	 */
	public $response = null;

	/**
	 * @var Node
	 */
	public $responseForm = null;

	/**
	 * check if user is authorized
	 *
	 * @return bool
	 */
	abstract public function access():bool;

	/**
	 * This method will call if nothing is submitted.
	 *
	 * @return void
	 */
	abstract public function setUp();

	/**
	 * array[name:string] = pattern:string
	 *
	 * @see    AbstractSitePage::checkExpectation
	 * @return array (see above)
	 */
	abstract public function getExpectList():array;

	/**
	 * This method will call if:
	 * - count of $_POST is greater than zero
	 * - formId and all expectations are correct
	 *
	 * @return bool
	 */
	abstract public function check():bool;

	/**
	 * @param Bucket $config
	 * @param array  $request
	 */
	public function __construct(Bucket $config, array $request) {
		$this->request = $request;
		$this->config  = $config;

		$this->response     = new Tree('document');
		$this->responseForm = $this->response->getRootNode()->addChild(new Node('form'));
	}

	/**
	 * @param  string $name
	 * @param  string $value
	 * @return AbstractSitePage this
	 */
	final public function setResponseFormItem(string $name, string $value):AbstractSitePage {
		$this->responseForm
				->addChild(new Node($name))
				->setContent($value);
		return $this;
	}

	/**
	 * Call this method in <code>AbstractSitePage::check</code> to indicate: the request was <b>successful</b>.
	 * Return the response of this method!
	 *
	 * @param  string $reason
	 * @return bool
	 */
	final public function accept(string $reason = null):bool {
		if (is_string($reason)) {
			$this->responseForm->setAttribute('reason', $reason);
		}
		return true;
	}

	/**
	 * Call this method in <code>AbstractSitePage::check</code> to indicate: the request was <b>failed</b>.
	 * Return the response of this method!
	 *
	 * @param  string $reason
	 * @return bool
	 */
	final public function reject(string $reason = null):bool {
		if (is_string($reason)) {
			$this->responseForm->setAttribute('reason', $reason);
		}
		return false;
	}

	/**
	 * @return bool
	 */
	public function checkExpectation():bool {
		$expectList           = $this->getExpectList();
		$expectList['formId'] = SiteModeHandler::FORM_ID_PATTERN;

		foreach ($expectList as $name => $pattern) {
			if (!isset($this->request[$name]) || !preg_match('/^'.$pattern.'$/', $this->request[$name])) {
				return false;
			}
		}
		return true;
	}

}
