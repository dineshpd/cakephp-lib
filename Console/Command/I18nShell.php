<?php
/**
 * Internationalization Management Shell
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @since         CakePHP(tm) v 1.2.0.5669
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
namespace Cake\Console\Command;

use Cake\Console\Shell;

/**
 * Shell for I18N management.
 *
 * @package       Cake.Console.Command
 */
class I18nShell extends Shell {

/**
 * Contains database source to use
 *
 * @var string
 */
	public $dataSource = 'default';

/**
 * Contains tasks to load and instantiate
 *
 * @var array
 */
	public $tasks = array('DbConfig', 'Extract');

/**
 * Override startup of the Shell
 *
 * @return mixed
 */
	public function startup() {
		$this->_welcome();
		if (isset($this->params['datasource'])) {
			$this->dataSource = $this->params['datasource'];
		}

		if ($this->command && !in_array($this->command, array('help'))) {
			if (!config('database')) {
				$this->out(__d('cake_console', 'Your database configuration was not found. Take a moment to create one.'), true);
				return $this->DbConfig->execute();
			}
		}
	}

/**
 * Override main() for help message hook
 *
 * @return void
 */
	public function main() {
		$this->out(__d('cake_console', '<info>I18n Shell</info>'));
		$this->hr();
		$this->out(__d('cake_console', '[E]xtract POT file from sources'));
		$this->out(__d('cake_console', '[I]nitialize i18n database table'));
		$this->out(__d('cake_console', '[H]elp'));
		$this->out(__d('cake_console', '[Q]uit'));

		$choice = strtolower($this->in(__d('cake_console', 'What would you like to do?'), array('E', 'I', 'H', 'Q')));
		switch ($choice) {
			case 'e':
				$this->Extract->execute();
			break;
			case 'i':
				$this->initdb();
			break;
			case 'h':
				$this->out($this->OptionParser->help());
			break;
			case 'q':
				exit(0);
			break;
			default:
				$this->out(__d('cake_console', 'You have made an invalid selection. Please choose a command to execute by entering E, I, H, or Q.'));
		}
		$this->hr();
		$this->main();
	}

/**
 * Initialize I18N database.
 *
 * @return void
 */
	public function initdb() {
		$this->dispatchShell('schema create i18n');
	}

/**
 * Get and configure the Option parser
 *
 * @return ConsoleOptionParser
 */
	public function getOptionParser() {
		$parser = parent::getOptionParser();
		return $parser->description(
			__d('cake_console', 'I18n Shell initializes i18n database table for your application and generates .pot files(s) with translations.')
			)->addSubcommand('initdb', array(
				'help' => __d('cake_console', 'Initialize the i18n table.')
			))->addSubcommand('extract', array(
				'help' => __d('cake_console', 'Extract the po translations from your application'),
				'parser' => $this->Extract->getOptionParser()
			));
	}

}
