<?php
/**
 * Koowa Framework - http://developer.joomlatools.com/koowa
 *
 * @copyright	Copyright (C) 2011 - 2013 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		http://github.com/joomlatools/koowa-files for the canonical source repository
 */

class ComFilesAdapterLocalIterator extends KObject
{
	public function getFiles(array $config = array())
	{
		$config['type'] = 'files';
		return self::getNodes($config);
	}

	public function getFolders(array $config = array())
	{
		$config['type'] = 'folders';
		return self::getNodes($config);
	}

	public function getNodes(array $config = array())
	{
		$config['path'] = $this->getService('com://admin/files.adapter.local.folder',
					array('path' => $config['path']))->getRealPath();

		try {
			$results = ComFilesIteratorDirectory::getNodes($config);
		}
		catch (Exception $e) {
			return false;
		}

		return $results;
	}
}
