<?php
/**
 * Nooku Framework - http://nooku.org/framework
 *
 * @copyright	Copyright (C) 2011 - 2014 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		http://github.com/joomlatools/joomlatools-framework-files for the canonical source repository
 */

/**
 * Thumbnails Model
 *
 * @author  Ercan Ozkaya <https://github.com/ercanozkaya>
 * @package Koowa\Component\Files
 */
class ComFilesModelThumbnails extends ComFilesModelFiles
{
    protected $_source;

    public function __construct(KObjectConfig $config)
    {
        parent::__construct($config);

        $state = $this->getState();

        $state->insert('version', 'cmd')
              ->insert('source', 'string');
    }

    protected function _initialize(KObjectConfig $config)
    {
        $config->append(array('state' => 'com:files.model.state.thumbnails'));
        parent::_initialize($config);
    }

    protected function _actionCreate(KModelContext $context)
    {
        $parameters = $this->getContainer()->getParameters();
        $state      = $this->getState();
        $entity     = $context->getEntity();

        $entity->name   = $state->name;
        $entity->folder = $state->folder;

        if ($source = $this->_getSource()) {
            $entity->source = $source;
        }

        if ($versions = $parameters->versions)
        {
            if ($version = $state->version)
            {
                if ($config = $versions->{$version})
                {
                    $entity->version   = $version;
                    $entity->name      = $version . '-' . $entity->name;
                    $entity->dimension = $config->dimension->toArray();
                    $entity->crop      = $config->crop;
                }
            }
        }
        else
        {
            if ($dimension = $parameters->dimension) {
                $entity->dimension = $dimension;
            }

            if (isset($parameters->crop)) {
                $entity->crop = $parameters->crop;
            }
        }

        return parent::_actionCreate($context);
    }

    /**
     * Reset the cached container object if container changes
     *
     * @param KModelContextInterface $context
     */
    protected function _afterReset(KModelContextInterface $context)
    {
        parent::_afterReset($context);

        $modified = (array) KObjectConfig::unbox($context->modified);
        if (in_array('source', $modified)) {
            $this->_source = null;
        }
    }

    protected function _getSource()
    {
        if (!$this->_source instanceof ComFilesModelEntityFile)
        {
            $state = $this->getState();

            if ($state->source)
            {
                $file = $this->getObject('com:files.model.files')
                             ->container($state->getSourceContainer()->slug)
                             ->folder($state->folder)
                             ->name(basename($state->name, '.jpg'))
                             ->fetch();

                if (!$file->isNew()) {
                    $this->_source = $file;
                }
            }
        }

        return $this->_source;
    }

    protected function _beforeCreateSet(KModelContextInterface $context)
    {
        $parameters = $this->getContainer()->getParameters();

        if ($thumbnails = $context->files)
        {
            $source = $this->_getSource();

            foreach ($thumbnails as $thumbnail)
            {
                if ($source) {
                    $thumbnail->source = $source;
                }

                if ($versions = $parameters->versions)
                {
                    $versions = array_keys($versions->toArray());

                    foreach ($versions as $version)
                    {
                        if (strpos($thumbnail->name, $version) === 0) {
                            break;
                        }
                    }

                    $config = $parameters->versions->{$version};

                    $thumbnail->dimension = $config->dimension;
                    $thumbnail->crop      = $config->crop;
                    $thumbnail->version   = $version;
                }
                else
                {
                    if ($dimension = $parameters->dimension) {
                        $thumbnail->dimension = $dimension;
                    }

                    if (isset($parameters->crop)) {
                        $thumbnail->crop = $parameters->crop;
                    }
                }
            }
        }
    }

    public function iteratorFilter($path)
    {
        $state     = $this->getState();
        $filename  = ltrim(basename(' '.strtr($path, array('/' => '/ '))));

        if ($filename && $filename[0] === '.') {
            return false;
        }

        if ($name = $state->name)
        {
            $names = array();

            $parameters = $this->getContainer()->getParameters();

            if ($parameters->versions)
            {
                if ($version = $state->version) {
                    $versions = (array) $version;
                } else {
                    $versions = array_keys($parameters->versions->toArray());
                }

                foreach ($versions as $version) {
                    $names[] = $version . '-' . $name;
                }
            }
            else $names[] = $name;

            if (!in_array($filename, $names)) {
                return false;
            }
        }

        if ($state->search && stripos($filename, $state->search) === false) {
            return false;
        }
    }
}
