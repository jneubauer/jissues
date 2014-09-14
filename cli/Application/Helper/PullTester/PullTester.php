<?php
/**
 * Part of the Joomla! Tracker application.
 *
 * @copyright  Copyright (C) 2014 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Application\Helper\PullTester;

use PHP_CodeSniffer_CLI;

/**
 * Class PullTester.
 *
 * @since  1.0
 */
class PullTester
{
	/**
	 * Joomla! Github object
	 *
	 * @var    \JTracker\GitHub\Github
	 *
	 * @since  1.0
	 */
	protected $gitHub;

	/**
	 * @var string
	 *
	 * @since  1.0
	 */
	private $owner;

	/**
	 * @var string
	 *
	 * @since  1.0
	 */
	private $repo;

	/**
	 * @var PullRequest
	 */
	private $pull = null;

	/**
	 * Constructor.
	 *
	 * @param   string  $owner  The repo owner.
	 * @param   string  $repo   The repo name.
	 */
	public function __construct($owner, $repo)
	{
		$this->owner = $owner;
		$this->repo  = $repo;
	}

	public function runTests($pullNumber)
	{
		$basePath = JPATH_ROOT . '/build/tests/' . $this->owner . '/' . $this->repo . '/' . $pullNumber;

		$csErrors = $this->runCheckStyle($basePath);


		echo $csErrors;

		return;

		$this
			->out()
			->out(
				$numErrors
					? sprintf('<error> Finished with %d errors </error>', $numErrors)
					: '<ok>Success</ok>'
			);

	}

	/**
	 * Run checkstyle tests.
	 *
	 * @param $basePath
	 *
	 * @return  integer The number of error and warning messages shown.
	 */
	private function runCheckStyle($basePath)
	{
		$options = [
			'files'      => [$basePath],
			'extensions' => ['php'],
			'standard'   => [JPATH_ROOT . '/build/phpcs/Joomla'],
			'reports'    => ['xml' => $basePath . '/checkstyle.xml']
		];

		$phpCS = new PHP_CodeSniffer_CLI;

		$phpCS->checkRequirements();

		$numErrors = $phpCS->process($options);

		return $numErrors;
	}
}
