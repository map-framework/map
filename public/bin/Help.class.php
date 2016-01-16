<?php

use store\data\File;

final class Help extends AbstractCommand {

	const SYNTAX						= '[COMMAND_NAME]...';
	const DESCRIPTION 			= 'overview & informations about commands';

	const COMMANDS_PUBLIC		= 'public/bin/';
	const COMMANDS_PRIVATE	= 'private/bin/';

	/**
	 * @see    AbstractCommand::handle($optionList)
	 * @param  string[] $optionList
	 * @return Help
	 */
	public function handle($optionList) {
		$this->headline('Help');

		$commandList = array();

		foreach ($optionList as $option) {
			# check command
			try {
				$command = Console::getCommand($option);
			}
			catch (Exception $e) {
				$this->info($e->getMessage());
				continue;
			}

			# sort: only one of each command
			if (!in_array($command, $commandList)) {
				$commandList[] = $command;
			}
		}

		# print commands or overview
		if (count($commandList)) {
			foreach ($commandList as $command) {
				$this->printCommand($command);
			}
		}
		else {
			$this->printOverview();
		}

		return $this;
	}

	/**
	 * @param  AbstractCommand $command
	 * @return Help
	 */
	private function printCommand(AbstractCommand $command) {
		$this->subHeadline(ucfirst($command->getName()));

		$syntax 			= get_class($command).'::SYNTAX';
		$description 	= get_class($command).'::DESCRIPTION';

		# print syntax
		if (defined($syntax)) {
			$this->bold($this->getName().' '.constant($syntax));
		}
		else {
			$this->italic('syntax unknown');
		}
		$this->outln(null, 2);

		# print description
		if (defined($description)) {
			$this->out(constant($description));
		}
		else {
			$this->italic('description unknown');
		}
		return $this->outln(null, 2);
	}

	/**
	 * @return Help
	 */
	private function printOverview() {
		$this->subHeadline('Overview');

		$commandDirectoryList = array(
			new File(self::COMMANDS_PUBLIC),
			new File(self::COMMANDS_PRIVATE)
		);
		$commandList = array();

		# loop: public and private directory
		foreach ($commandDirectoryList as $commandDirectory) {

			try {
				$fileList = $commandDirectory->scan(File::TYPE_FILE);
			}
			catch (Exception $e) {
				$this->error($e->getMessage());
				continue;
			}

			# loop: files in directory
			foreach ($fileList as $file) {
				$fileSplitted = preg_split('/(\/|\.class\.php)/', $file);
				try {
					# -2 because last is `.class.php` and before `ClassName`
					$commandObject = Console::getCommand($fileSplitted[count($fileSplitted) - 2]);
				}
				catch (Exception $e) {
					continue;
				}

				$commandList[] = $commandObject->getName();
			}
		}
		return $this->unsortList($commandList);
	}

}