<?php
/**
 * Part of the Joomla Framework GitHub Package
 *
 * @copyright  Copyright (C) 2014 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace JTracker\Github\DataType;

use JTracker\Github\DataType\PullRequest\Head;

/**
 * Class GitHub DataType PullRequest.
 *
 * @since  1.0
 */
class PullRequest
{
	/**
	 * @var Head
	 */
	public $head = null;

	/**
	 * @var string
	 */
	public $diff_url = '';

	/**
	 * @var integer
	 */
	public $number = 0;
}
