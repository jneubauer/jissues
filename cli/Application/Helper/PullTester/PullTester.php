<?php
/**
 * Part of the Joomla! Tracker application.
 *
 * @copyright  Copyright (C) 2014 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Application\Helper\PullTester;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use PHP_CodeSniffer_CLI;

/**
 * Class PullTester.
 *
 * @since  1.0
 */
class PullTester
{
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
	 * Constructor.
	 *
	 * @param   string  $owner  The repo owner.
	 * @param   string  $repo   The repo name.
	 *
	 * @since  1.0
	 */
	public function __construct($owner, $repo)
	{
		$this->owner = $owner;
		$this->repo  = $repo;
	}

	/**
	 * Run the tests.
	 *
	 * @param   integer  $pullNumber  The pull number.
	 *
	 * @return  $this
	 *
	 * @throws \Exception
	 *
	 * @since  1.0
	 */
	public function runTests($pullNumber)
	{
		$basePath = 'build/tests/' . $this->owner . '/' . $this->repo . '/' . $pullNumber;

		$csErrors = $this->runCheckStyle(JPATH_ROOT . '/' . $basePath);

		$tests = new \stdClass;

		$tests->checkstyleErrors = $csErrors;

		$filesystem = new Filesystem(new Local(JPATH_ROOT));

		// Store the file to disk
		if (false == $filesystem->write($basePath . '/tests.json', json_encode($tests, JSON_PRETTY_PRINT)))
		{
			throw new \Exception(sprintf('Can not write the file: "%s"', $basePath . '/tests.json'));
		}

		return $this;
	}

	/**
	 * Run checkstyle tests.
	 *
	 * @param   string  $basePath  The base path.
	 *
	 * @return  integer The number of error and warning messages shown.
	 *
	 * @since  1.0
	 */
	private function runCheckStyle($basePath)
	{
		$options = [
			'files'      => [$basePath . '/files'],
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
