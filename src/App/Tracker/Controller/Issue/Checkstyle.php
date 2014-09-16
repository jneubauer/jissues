<?php
/**
 * Part of the Joomla Tracker's Tracker Application
 *
 * @copyright  Copyright (C) 2012 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace App\Tracker\Controller\Issue;

use App\Tracker\Model\CheckstyleModel;
use App\Tracker\View\Checkstyle\CheckstyleHtmlView;

use JTracker\Controller\AbstractTrackerController;

/**
 * Item controller class for the Tracker component.
 *
 * @since  1.0
 */
class Checkstyle extends AbstractTrackerController
{
	/**
	 * The default view for the component
	 *
	 * @var    string
	 * @since  1.0
	 */
	protected $defaultView = 'checkstyle';

	/**
	 * View object
	 *
	 * @var    CheckstyleHtmlView
	 * @since  1.0
	 */
	protected $view = null;

	/**
	 * Model object
	 *
	 * @var    CheckstyleModel
	 * @since  1.0
	 */
	protected $model = null;

	/**
	 * Initialize the controller.
	 *
	 * This will set up default model and view classes.
	 *
	 * @return  $this  Method supports chaining
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	public function initialize()
	{
		parent::initialize();

		/* @type \JTracker\Application $application */
		$application = $this->getContainer()->get('app');
		$project     = $application->getProject();

		$this->view->setProject($project);
		$this->model->setProject($project);

		$application->getUser()->authorize('view');

		$id = $application->input->getUint('id');

		$path = JPATH_ROOT . '/build/tests/' . $project->gh_user . '/' . $project->gh_project . '/' . $id;

		if (false == file_exists($path . '/checkstyle.xml'))
		{
			throw new \RuntimeException('No checkstyle report available for this item' . $path);
		}

		$phpcs = simplexml_load_file($path . '/checkstyle.xml');

		$this->view->setCheckstyleObject($phpcs);
		$this->view->setItem($this->model->getItem($id));

		return $this;
	}
}
