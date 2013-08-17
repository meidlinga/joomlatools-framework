<?php
/**
 * Koowa Framework - http://developer.joomlatools.com/koowa
 *
 * @copyright	Copyright (C) 2007 - 2013 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		http://github.com/joomlatools/koowa for the canonical source repository
 */


/**
 * Default Controller
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Component\Koowa
 */
class ComKoowaControllerDefault extends KControllerService
{
	/**
	 * The limit information
	 *
	 * @var	array
	 */
	protected $_limit;

	/**
	 * Constructor
	 *
	 * @param   KConfig $config Configuration options
	 */
	public function __construct(KConfig $config)
	{
		parent::__construct($config);

		$this->_limit = $config->limit;

        if($this->isDispatched())
        {
            if($config->persistable) {
                $this->addBehavior('persistable');
            }

            if(!JFactory::getUser()->guest)
            {
                $this->attachToolbars(); //attach the toolbars
                $this->registerCallback('after.get' , array($this, 'renderToolbars'));
            }
        }
	}

	/**
     * Initializes the default configuration for the object
     *
     * Called from {@link __construct()} as a first step of object instantiation.
     *
     * @param   KConfig $config Configuration options
     * @return void
     */
    protected function _initialize(KConfig $config)
    {
        //Disable controller persistency on non-HTTP requests,
        //e.g. AJAX, and requests containing the tmpl variable set to component (modal boxes)
        if(JFactory::getApplication()->isAdmin())
        {
            $persistable = (KRequest::type() == 'HTTP' && KRequest::get('get.tmpl','cmd') != 'component');
            $config->append(array(
                'persistable'    => $persistable,
            ));
        }

        //Set the maximum list limit to 100
        $config->append(array(
            'limit' => array('max' => 100, 'default' => JFactory::getApplication()->getCfg('list_limit'))
        ));

        parent::_initialize($config);
    }

    /**
     * Display action
     *
     * If the controller was not dispatched manually load the languages files
     *
     * @param   KCommandContext $context A command context object
     * @return 	string|bool 	The rendered output of the view or FALSE if something went wrong
     */
    protected function _actionGet(KCommandContext $context)
    {
        $this->getService('translator')->loadLanguageFiles($this->getIdentifier());
        return parent::_actionGet($context);
    }

	/**
     * Browse action
     *
     * Use the application default limit if no limit exists in the model and limit the limit to a maximum.
     *
     * @param   KCommandContext $context A command context object
     * @return 	KDatabaseRowsetInterface	A rowset object containing the selected rows
     */
    protected function _actionBrowse(KCommandContext $context)
    {
        if($this->isDispatched())
        {
            $limit = $this->getModel()->get('limit');

            //If limit is empty use default
            if(empty($limit)) {
                $limit = $this->_limit->default;
            }

            //Force the maximum limit
            if($limit > $this->_limit->max) {
                $limit = $this->_limit->max;
            }

            $this->limit = $limit;
        }

        return parent::_actionBrowse($context);
    }

    /**
     * Read action
     *
     * This functions implements an extra check to hide the main menu is the view name is singular (item views)
     *
     * @param  KCommandContext $context A command context object
     * @return KDatabaseRowInterface A row object containing the selected row
     */
    protected function _actionRead(KCommandContext $context)
    {
        //Perform the read action
        $row = parent::_actionRead($context);

        //Add the notice if the row is locked
        if(JFactory::getApplication()->isAdmin() && isset($row))
        {
            if(!isset($this->_request->layout) && $row->isLockable() && $row->locked()) {
                JFactory::getApplication()->enqueueMessage($row->lockMessage(), 'notice');
            }
        }

        return $row;
    }

    /**
     * Attach the toolbars to the controller
     * .
     * void
     */
    public function attachToolbars()
    {
        if ($this->getView() instanceof KViewHtml)
        {
            $this->attachToolbar($this->getView()->getName());

            if(JFactory::getApplication()->isAdmin()) {
                $this->attachToolbar('menubar');
            };
        }
    }

    /**
     * Run the toolbar filter to convert toolbars to HTML in the template
     * .
     * @param   KCommandContext	$context A command context object
     */
    public function renderToolbars(KCommandContext $context)
    {
        if ($this->getView() instanceof KViewHtml)
        {
            $filter = $this->getView()
                ->getTemplate()
                ->getFilter('toolbar')
                ->setToolbars($this->getToolbars());

            $result = $context->result;
            $filter->write($result);
            $context->result = $result;
        }
    }

	/**
     * Set a request property
     *
     *  This function translates 'limitstart' to 'offset' for compatibility with Joomla
     *
     * @param  	string 	$property The property name.
     * @param 	mixed 	$value    The property value.
     */
 	public function __set($property, $value)
    {
        if($property == 'limitstart') {
            $property = 'offset';
        }

        parent::__set($property, $value);
  	}
}
