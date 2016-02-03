<?php
namespace extension;

use exception\request\AcceptedException;
use exception\request\RejectedException;
use store\Bucket;

abstract class AbstractPage {

	/**
	 * initialized question
	 */
	const STATUS_INIT     = 'INIT';

	/**
	 * restored / reloaded question
	 */
	const STATUS_RESTORED = 'RESTORED';

	/**
	 * negative reply
	 * @see exception\request\AcceptedException
	 */
	const STATUS_REJECTED = 'REJECTED';

	/**
	 * positive reply
	 * @see exception\request\AcceptedException
	 */
	const STATUS_ACCEPTED = 'ACCEPTED';

	/**
	 * neutral reply
	 * @see exception\request\AcceptedException
	 */
	const STATUS_REPEATED = 'REPEATED';

	/**
	 * @var string[]
	 */
	private $expect       = array();

	/**
	 * @var array
	 */
	protected $request    = array();

	/**
	 * @var Bucket
	 */
	public $response      = null;

	/**
	 * check if user is entitled
	 * @return bool
	 */
	abstract public function access();

	/**
	 * Call if nothing submitted.
	 * @return void
	 */
	abstract public function setUp();

	/**
	 * Call if submitted.
	 * @throws RejectedException
	 * @throws AcceptedException
	 * @return bool
	 */
	abstract public function check();

	/**
	 * @param array $request
	 */
	public function __construct($request) {
		$this->addExpect('formId');
		$this->request  = $request;
		$this->response = new Bucket();
	}

	/**
	 * @param  string $formItemName
	 * @param  string $pattern
	 * @return AbstractPage
	 */
	final protected function addExpect($formItemName, $pattern = '.*') {
		$this->expect[$formItemName] = $pattern;
	}

	/**
	 * @throws RejectedException
	 * @return void
	 */
	public function checkExpectation() {
		foreach ($this->expect as $formItemName => $pattern) {
			if (!isset($this->request[$formItemName]) || !preg_match('/^'.$pattern.'$/', $this->request[$formItemName])) {
				throw new RejectedException();
			}
		}
	}

}
