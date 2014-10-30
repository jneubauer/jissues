<?php
/**
 * Part of the Joomla! Tracker application.
 *
 * @copyright  Copyright (C) 2014 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Application\Helper\GitHub;

use Joomla\Github\Http;
use Joomla\Http\HttpFactory;
use Joomla\Http\Transport\Curl;

use JTracker\Github\DataType\Commit;
use JTracker\Github\DataType\Commit\Status;
use JTracker\Github\DataType\PullRequest;
use JTracker\GitHub\Github;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;

/**
 * Class for processing pull requests on GitHub.
 *
 * @since  1.0
 */
class PullProcessor
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
	 *
	 * @since  1.0
	 */
	private $pull = null;

	/**
	 * Constructor.
	 *
	 * @param   Github  $gitHub  The GitHub object.
	 * @param   string  $owner   The repo owner.
	 * @param   string  $repo    The repo name.
	 */
	public function __construct(Github $gitHub, $owner, $repo)
	{
		$this->gitHub = $gitHub;
		$this->owner  = $owner;
		$this->repo   = $repo;

		// The cURL extension is required to properly work.
		$transport = HttpFactory::getAvailableDriver([], ['curl']);

		// Check if we *really* got a cURL transport...
		if (!($transport instanceof Curl))
		{
			throw new \RuntimeException('Please enable cURL.');
		}

		$this->transport = new Http([], $transport);
	}

	/**
	 * Fetch a pull request.
	 *
	 * @param   integer  $number  The pull request number.
	 *
	 * @throws \Exception
	 *
	 * @return  PullRequest
	 *
	 * @since   1.0
	 */
	public function fetchPull($number)
	{
		// Get the pull request object
		$pull = $this->gitHub->pulls->get($this->owner, $this->repo, $number);

		if (is_null($pull->head->repo))
		{
			throw new \RuntimeException('REPO IS GONE', 666);
		}

		$this->pull = $pull;

		return $this->pull;
	}

	/**
	 * Fetch the files for a pull request.
	 *
	 * @return  $this
	 *
	 * @throws  \Exception
	 */
	public function fetchFiles()
	{
		$this->checkForPull();

		// Get the patch
		$patch = $this->transport->get($this->pull->diff_url)->body;

		// Get the file list
		$files = $this->parsePatch($patch);

		if (!$files)
		{
			return $this;
		}

		$filesystem = new Filesystem(new Local(JPATH_ROOT));

		$baseUrl = 'https://raw.github.com/' . $this->pull->head->user->login . '/' . $this->pull->head->repo->name . '/' . $this->pull->head->ref;

		$basePath = 'build/tests/' . $this->owner . '/' . $this->repo . '/' . $this->pull->number;

		$filesystem->deleteDir($basePath);
		$filesystem->createDir($basePath . '/files');

		// On "some" servers we might want to switch to pure PHP...
		$useTransport = ('openshift' == trim(getenv('JTRACKER_ENVIRONMENT'))) ? false : true;

		foreach ($files as $file)
		{
			switch ($file->action)
			{
				case 'modified':
				case 'added':
					// Get the file contents
					$contents = $useTransport
						? $this->transport->get($baseUrl . '/' . $file->new)->body
						: file_get_contents(urlencode($baseUrl . '/' . $file->new));

					// Store the file to disk
					if (false == $filesystem->write($basePath . '/files/' . $file->new, $contents))
					{
						throw new \Exception(sprintf('Can not write the file: "%s"', $basePath . '/' . $file->new));
					}

					break;

				case 'deleted':
					break;

				default:
					throw new \RuntimeException(sprintf('Unknown action: "%s"', $file->action));
					break;
			}
		}

		return $this;
	}

	/**
	 * Get the GitHub merge status for an issue.
	 *
	 * @return  Status
	 *
	 * @since   1.0
	 */
	public function getMergeStatus()
	{
		$this->checkForPull();

		/* @type Status[] $statuses */
		$statuses = $this->gitHub->repositories->statuses->getList($this->owner, $this->repo, $this->pull->head->sha);

		$mergeStatus = new Status;

		if (isset($statuses[0]))
		{
			$mergeStatus->state       = $statuses[0]->state;
			$mergeStatus->targetUrl   = $statuses[0]->target_url;
			$mergeStatus->description = $statuses[0]->description;
			$mergeStatus->context     = $statuses[0]->context;
		}

		return $mergeStatus;
	}

	/**
	 * Get the commits for a GitHub pull request.
	 *
	 * @return  Commit[]
	 *
	 * @since   1.0
	 */
	public function getCommits()
	{
		$this->checkForPull();

		$commitData = $this->gitHub->pulls->getCommits($this->owner, $this->repo, $this->pull->number);

		$commits = [];

		foreach ($commitData as $commit)
		{
			$c = new Commit;

			$c->sha            = $commit->sha;
			$c->message        = $commit->commit->message;
			$c->author_name    = isset($commit->author->login) ? $commit->author->login : '';
			$c->author_date    = $commit->commit->author->date;
			$c->committer_name = isset($commit->committer->login) ? $commit->committer->login : '';
			$c->committer_date = $commit->commit->committer->date;

			$commits[] = $c;
		}

		return $commits;
	}

	/**
	 * Get the head ref for a pull.
	 *
	 * @return string
	 *
	 * @since   1.0
	 */
	public function getHeadRef()
	{
		return $this->checkForPull()
			->pull->head->ref;
	}

	/**
	 * Create a GitHub merge status for the last commit in a PR.
	 *
	 * @param   string  $state        The state (pending, success, error or failure).
	 * @param   string  $targetUrl    Optional target URL.
	 * @param   string  $description  Optional description for the status.
	 * @param   string  $context      A string label to differentiate this status from the status of other systems.
	 *
	 * @return  Status
	 *
	 * @since   1.0
	 */
	public function createStatus($state, $targetUrl, $description, $context)
	{
		$this->checkForPull();

		return $this->gitHub->repositories->statuses->create(
			$this->owner, $this->repo, $this->pull->head->sha,
			$state, $targetUrl, $description, $context
		);
	}

	/**
	 * Method to parse a patch and extract the affected files.
	 *
	 * @param   string  $patch  Patch file to parse.
	 *
	 * @return  array  Array of files within a patch
	 *
	 * @since   1.0
	 */
	private function parsePatch($patch)
	{
		$state = 0;
		$files = [];
		$file  = new \stdClass;

		$lines = explode("\n", $patch);

		foreach ($lines as $line)
		{
			switch ($state)
			{
				case 0:
					if (strpos($line, 'diff --git') === 0)
					{
						$state = 1;
						$file = new \stdClass;

						$file->action = 'modified';
					}

					break;

				case 1:
					if (strpos($line, 'index') === 0)
					{
						$file->index = substr($line, 6);
					}

					if (strpos($line, '---') === 0)
					{
						$file->old = substr($line, 6);
					}

					if (strpos($line, '+++') === 0)
					{
						$file->new = substr($line, 6);
					}

					if (strpos($line, 'new file mode') === 0)
					{
						$file->action = 'added';
					}

					if (strpos($line, 'deleted file mode') === 0)
					{
						$file->action = 'deleted';
					}

					if (strpos($line, '@@') === 0)
					{
						$state   = 0;

						$files[] = $file;
					}

					break;
			}
		}

		return $files;
	}

	/**
	 * Check if a pull request has been fetched.
	 *
	 * @return $this
	 *
	 * @since   1.0
	 */
	private function checkForPull()
	{
		if (!$this->pull)
		{
			throw new \UnexpectedValueException('No Pull set');
		}

		return $this;
	}
}
