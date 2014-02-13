<?php
/**
 * Koowa Framework - http://developer.joomlatools.com/koowa
 *
 * @copyright	Copyright (C) 2011 - 2013 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		http://github.com/joomlatools/koowa-files for the canonical source repository
 */

/**
 * Folders Model
 *
 * @author  Ercan Ozkaya <https://github.com/ercanozkaya>
 * @package Koowa\Component\Files
 */
class ComFilesModelFolders extends ComFilesModelNodes
{
	public function __construct(KObjectConfig $config)
	{
		parent::__construct($config);

		$this->getState()->insert('tree', 'boolean', false);
	}

	public function getList()
	{
		if (!isset($this->_list))
		{
			$state = $this->getState();

			$folders = $this->getContainer()->getAdapter('iterator')->getFolders(array(
				'path'    => $this->getPath(),
				'recurse' => !!$state->tree,
				'filter'  => array($this, 'iteratorFilter'),
				'map'     => array($this, 'iteratorMap'),
            	'sort'    => $state->sort
			));
        	if ($folders === false) {
        		throw new UnexpectedValueException('Invalid folder');
        	}
			$this->_total = count($folders);

			if (strtolower($state->direction) == 'desc') {
				$folders = array_reverse($folders);
			}

			$folders = array_slice($folders, $state->offset, $state->limit ? $state->limit : $this->_total);

			$results = array();
			foreach ($folders as $folder)
			{
				$hierarchy = array();
				if ($state->tree)
				{
					$hierarchy = explode('/', dirname($folder));
					if (count($hierarchy) === 1 && $hierarchy[0] === '.') {
						$hierarchy = array();
					}
				}

                $name = strpos($folder, '/') !== false ? substr($folder, strrpos($folder, '/')+1) : basename($folder);

				$results[] = array(
					'container' => $state->container,
					'folder' 	=> $hierarchy ? implode('/', $hierarchy) : $state->folder,
					'name' 		=> $name,
					'hierarchy' => $hierarchy
				);
			}

			$this->_list = $this->getRowset()->addData($results);
		}

		return parent::getList();
	}

	public function iteratorMap($path)
	{
		$path = str_replace('\\', '/', $path);
		$path = str_replace($this->getContainer()->path.'/', '', $path);

		return $path;
	}

	public function iteratorFilter($path)
	{
        $state    = $this->getState();
		$filename = basename($path);
		if ($state->name)
		{
			if (!in_array($filename, (array) $state->name)) {
				return false;
			}
		}

		if ($state->search && stripos($filename, $state->search) === false) {
			return false;
		}
	}
}
