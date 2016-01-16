<?php

use store\data\File;

final class Test extends AbstractCommand {

	const SYNTAX						= '[--public|--private]';
	const DESCRIPTION 			= 'run all, only public or only private unit tests';

	const TESTS_ALL					= '.';
	const TESTS_PUBLIC			= 'public/tests/';
	const TESTS_PRIVATE			= 'private/tests/';

	/**
	 * @see    AbstractCommand::handle($optionList)
	 * @param  string[] $optionList
	 * @return Test
	 */
	public function handle($optionList) {
		$this->headline('Tests (PHPUnit)');

		if (isset($optionList[0])) {
			if ($optionList[0] === '--public') {
				$directory = new File(self::TESTS_PUBLIC);
			}
			elseif ($optionList[0] === '--private') {
				$directory = new File(self::TESTS_PRIVATE);
			}
			else {
				$this->error('Option `'.$optionList[0].'` not exists. Try `help test` to fix it.', true);
			}
		}
		else {
			$directory = new File(self::TESTS_ALL);
		}

		return $this->outln(
			shell_exec('phpunit --exclude-group ignore --bootstrap '.Console::AUTOLOADER.' '.$directory)
		);
	}

}