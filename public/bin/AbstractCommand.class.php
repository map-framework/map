<?php

abstract class AbstractCommand {

	protected $terminalWidth	= null;
	protected $terminalHeight	= null;

	/**
	 * handle command
	 * @param  string[] $optionList
	 * @return AbstractCommand
	 */
	abstract public function handle($optionList);

	/**
	 * @return void
	 */
	public function __construct() {
		$this->terminalWidth 	= exec('tput cols');
		$this->terminalHeight	= exec('tput lines');
	}

	/**
	 * get command (short class name)
	 * @return string
	 */
	public function getName() {
		$reflection = new ReflectionClass($this);
		return strtolower($reflection->getShortName());
	}

	/**
	 * [block] print text and line break
	 * @param  string $message
	 * @param  int    $breaks count
	 * @return AbstractCommand
	 */
	final protected function outln($message = '', $breaks = 1) {
		$this->out($message);
		for ($i = 0; $i < $breaks; $i++) {
			$this->out(PHP_EOL);
		}
		return $this;
	}

	/**
	 * [block] print info message
	 * @param  string $message
	 * @return AbstractCommand
	 */
	final protected function info($message) {
		return $this->outln('[INFO]  '.$message);
	}

	/**
	 * [block] print error message
	 * @param  string $message
	 * @param  bool   $exit = false
	 * @return AbstractCommand
	 */
	final protected function error($message, $exit = false) {
		$this->outln('[ERROR] '.$message);
		if ($exit === true) {
			$this->info('early exit!');
			exit();
		}
		return $this;
	}

	/**
	 * [block] print headline
	 * @param  string $title
	 * @param  string $seperator = '='
	 * @return AbstractCommand
	 */
	final protected function headline($title, $separator = '=') {
		$this->outln($title);

		# separator / underline
		for ($i = 1; $i < strlen($title) && $i < $this->terminalWidth; $i++) {
			$this->out($separator);
		}
		$this->outln($separator, 2);
		
		return $this;
	}

	/**
	 * [block] print sub headline with minus separator
	 * @param  string $title
	 * @return AbstractCommand
	 */
	final protected function subHeadline($title) {
		return $this->headline($title, '-');
	}

	/**
	 * [block] print unsort list
	 * @param  string[] $itemList
	 * @param  string   $marker = '-'
	 * @return AbstractCommand
	 */
	final protected function unsortList($itemList, $marker = '-') {
		foreach ($itemList as $item) {
			$this->outln($marker.' '.$item);
		}
		return $this->outln();
	}

	/**
	 * [span]  print text
	 * @param  string $message
	 * @return AbstractCommand
	 */
	final protected function out($message) {
		echo $message;
		return $this;
	}

	/**
	 * [span]  print text with effect
	 * @link   http://tldp.org/LDP/abs/html/colorizing.html
	 * @param  string $message
	 * @return AbstractCommand
	 */
	final protected function outEffect($message, $effectNumber) {
		return $this
			->out("\033[".$effectNumber."m")
			->out($message)
			->out("\033[0m");
	}

	/**
	 * [span]  print bold text
	 * @param  string $message
	 * @return AbstractCommand
	 */
	final protected function bold($message) {
		return $this->outEffect($message, 1);
	}

	/**
	 * [span]  print italic text
	 * @param  string $message
	 * @return AbstractCommand
	 */
	final protected function italic($message) {
		return $this->outEffect($message, 3);
	}

	/**
	 * [span]  print underline text
	 * @param  string $message
	 * @return AbstractCommand
	 */
	final protected function underline($message) {
		return $this->outEffect($message, 4);
	}

	/**
	 * [span]  print strikeout text
	 * @param  string $message
	 * @return AbstractCommand
	 */
	final protected function strikeout($message) {
		return $this->outEffect($message, 9);
	}

}