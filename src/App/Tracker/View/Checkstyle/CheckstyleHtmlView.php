<?php
/**
 * Part of the Joomla Tracker's Tracker Application
 *
 * @copyright  Copyright (C) 2012 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace App\Tracker\View\Checkstyle;

use App\Projects\TrackerProject;
use App\Tracker\Model\IssueModel;
use App\Tracker\Table\IssuesTable;

use JTracker\View\AbstractTrackerHtmlView;

/**
 * The issues item view
 *
 * @since  1.0
 */
class CheckstyleHtmlView extends AbstractTrackerHtmlView
{
	/**
	 * Project object
	 *
	 * @var    TrackerProject
	 * @since  1.0
	 */
	protected $project = null;

	/**
	 * Item object
	 *
	 * @var    IssuesTable
	 * @since  1.0
	 */
	protected $item = null;

	/**
	 * If the user has "edit own" rights.
	 *
	 * @var    object
	 * @since  1.0
	 */
	protected $checkstyleObject = null;

	/**
	 * Method to render the view.
	 *
	 * @return  string  The rendered view.
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	public function render()
	{
		$this->renderer->set('project', $this->getProject());
		$this->renderer->set('item', $this->getItem());
		$this->renderer->set('checkstyleObject', $this->getCheckstyleObject());

		return parent::render();
	}

	/**
	 * Get the project.
	 *
	 * @return  TrackerProject
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	public function getProject()
	{
		if (is_null($this->project))
		{
			throw new \RuntimeException('No project set.');
		}

		return $this->project;
	}

	/**
	 * Set the project.
	 *
	 * @param   TrackerProject  $project  The project.
	 *
	 * @return  $this  Method allows chaining
	 *
	 * @since   1.0
	 */
	public function setProject(TrackerProject $project)
	{
		$this->project = $project;

		return $this;
	}

	/**
	 * Set if the user is allowed to edit her own issues.
	 *
	 * @param   object  $checkstyleObject  The checkstyle object.
	 *
	 * @return  $this
	 *
	 * @since    1.0
	 */
	public function setCheckstyleObject($checkstyleObject)
	{
		$this->checkstyleObject = $checkstyleObject;

		return $this;
	}

	/**
	 * Get the checkstyle object.
	 *
	 * @return object
	 *
	 * @since    1.0
	 */
	public function getCheckstyleObject()
	{
		if (is_null($this->checkstyleObject))
		{
			throw new \RuntimeException('Checkstyle Object not set.');
		}

		return $this->checkstyleObject;
	}

	/**
	 * Get the item.
	 *
	 * @throws \RuntimeException
	 * @return IssuesTable
	 *
	 * @since   1.0
	 */
	public function getItem()
	{
		if (is_null($this->item))
		{
			throw new \RuntimeException('Item not set.');
		}

		return $this->item;
	}

	/**
	 * Set the item.
	 *
	 * @param   IssuesTable  $item  The item to set.
	 *
	 * @return $this
	 *
	 * @since   1.0
	 */
	public function setItem($item)
	{
		$this->item = $item;

		return $this;
	}
}
