<?php
/**
 * Koowa Framework - http://developer.joomlatools.com/koowa
 *
 * @copyright	Copyright (C) 2011 - 2013 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		http://github.com/joomlatools/koowa-files for the canonical source repository
 */

/**
 * Files Html View
 *
 * @author  Ercan Ozkaya <https://github.com/ercanozkaya>
 * @package Koowa\Component\Files
 */
class ComFilesViewFilesHtml extends ComKoowaViewHtml
{
	protected function _initialize(KObjectConfig $config)
	{
		$config->auto_fetch = false;

		parent::_initialize($config);
	}

    public function fetchData(KViewContext $context)
	{
	    $state = $this->getModel()->getState();
	    if (empty($state->limit)) {
	        $state->limit = JFactory::getApplication()->getCfg('list_limit');
	    }
	     
		$context->data->sitebase  = trim(JURI::root(), '/');
		$context->data->token     = JSession::getFormToken();
		$context->data->container = $this->getModel()->getContainer();

		return parent::fetchData($context);
	}
}
