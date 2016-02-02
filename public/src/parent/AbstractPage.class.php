<?php
namespace parent;

use exception\request\AcceptedException;
use exception\request\RejectedException;

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
	protected $formData   = array();

	/**
	 * @var string[]
	 */
	private $expect       = array();

	/**
	 * response parameter list
	 * @var string[]
	 */
	private $paramList    = array();

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
	 * @param  array $formData
	 * @throws RejectedException if invalid
	 */
	public function __construct($formData) {
		foreach ($this->expect as $formItemName => $pattern) {
			if (!isset($this->formData[$formItemName]) || !preg_match('/^'.$pattern.'$/', $this->formData[$formItemName])) {
				throw new RejectedException();
			}
		}

		$this->formData = $formData;
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
	 * @param  mixed $param
	 * @return AbstractPage
	 */
	final protected function addParam($param) {
		$this->paramList[] = $param;
		return $this;
	}

	/**
	 * @return array
	 */
	final public function getParamList() {
		return $this->paramList;
	}

}
