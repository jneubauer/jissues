<?php
/**
 * Part of the Joomla Tracker Model Package
 *
 * @copyright  Copyright (C) 2014 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace App\Tracker\Controller\Fetch\Ajax;

use Joomla\Uri\Uri;
use JTracker\Controller\AbstractAjaxController;

/**
 * Controller to respond AJAX request.
 *
 * @since  1.0
 */
class Languages extends AbstractAjaxController
{
	/**
	 * Prepare the response.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	protected function prepareResponse()
	{
		/* @type \JTracker\Application $application */
		$application = $this->getContainer()->get('app');

		$languages = $application->get('languages');

		$langArray = [];

		foreach ($languages as $lang)
		{
			if ('en-GB' == $lang)
			{
				continue;
			}

			$langArray[] = $this->parseLanguageFile($lang);
		}

		$data = new \stdClass;

		$data->languages = $langArray;
		$data->fooo = 'bar';

		$this->response->data = $data;
	}

	/**
	 * Parse a language file and extract valuable information.
	 *
	 * @param   string  $lang  The language tag.
	 *
	 * @return \stdClass
	 */
	private function parseLanguageFile($lang)
	{
		$l = new \stdClass;

		$l->code = $lang;
		$l->name = '?';
		$l->link = '?';

		$path = JPATH_ROOT . '/src/JTracker/g11n/' . $lang . '/' . $lang . '.JTracker.po';

		if (false == file_exists($path))
		{
			return $l;

			// @throw new \DomainException('Language file not found in path: ' . $path);
		}

		$contents = file_get_contents($path);

		if (preg_match('@Language-Team: (.+) \((.+)\)@', $contents, $matches))
		{
			$l->name = $matches[1];
			$l->link = $matches[2];
		}

		return $l;
	}
}
