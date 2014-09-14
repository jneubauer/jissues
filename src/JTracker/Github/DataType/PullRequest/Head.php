<?php
/**
 * Part of the Joomla Framework GitHub Package
 *
 * @copyright  Copyright (C) 2014 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace JTracker\Github\DataType\PullRequest;

use JTracker\Github\DataType\Repo;
use JTracker\Github\DataType\User;

/**
 * Class GitHub DataType PullRequest Head.
 *
 * @since  1.0
 */
class Head
{
	/**
	 * @var Repo
	 */
	public $repo = '';

	/**
	 * @var User
	 */
	public $user = null;

	/**
	 * @var string
	 */
	public $ref = '';

	/**
	 * @var string
	 */
	public $sha = '';
}
