<?php
/**
 * @version     $Id$
 * @package     Nooku_Components
 * @subpackage  Files
 * @copyright   Copyright (C) 2011 - 2012 Timble CVBA and Contributors. (http://www.timble.net).
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.nooku.org
 */

/**
 * File Validator Command Class
 *
 * @author      Ercan Ozkaya <http://nooku.assembla.com/profile/ercanozkaya>
 * @package     Nooku_Components
 * @subpackage  Files
 */
class ComFilesCommandValidatorFile extends ComFilesCommandValidatorNode
{
	protected function _databaseBeforeSave(KCommandContext $context)
	{
		$row = $context->caller;

		if (is_string($row->file) && !is_uploaded_file($row->file))
		{
			// remote file
            $file = $this->getService('com://admin/files.database.row.url');
            $file->setData(array('file' => $row->file));

            if (!$file->load()) {
                throw new KControllerExceptionActionFailed('File cannot be downloaded');
            }

            $row->contents = $file->contents;

			if (empty($row->name))
			{
				$uri = $this->getService('koowa:http.url', array('url' => $row->file));
	        	$path = $uri->toString(KHttpUrl::PATH | KHttpUrl::FORMAT);
	        	if (strpos($path, '/') !== false) {
	        		$path = basename($path);
	        	}

	        	$row->name = $path;
			}
		}

		return parent::_databaseBeforeSave($context) && $this->getService('com://admin/files.filter.file.uploadable')->validate($context);

	}
}