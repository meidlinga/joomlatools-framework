<?php
/**
 * Koowa Framework - http://developer.joomlatools.com/koowa
 *
 * @copyright	Copyright (C) 2007 - 2013 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		http://github.com/joomlatools/koowa for the canonical source repository
 */

/**
 * Table Database Row
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Library\Database
 */
class KDatabaseRowTable extends KDatabaseRowAbstract
{
	/**
	 * Table object or identifier (com://APP/COMPONENT.table.NAME)
	 *
	 * @var	string|object
	 */
	protected $_table = false;

	/**
     * Object constructor
     *
     * @param   KObjectConfig $config Configuration options.
     */
	public function __construct(KObjectConfig $config = null)
	{
        //Bypass DatabaseRowAbstract constructor to prevent data from being added twice
        KObject::__construct($config);

        //Set the table identifier
        $this->_table = $config->table;

        // Set the table identifier
        if (isset($config->identity_column)) {
            $this->_identity_column = $config->identity_column;
        }

        // Reset the row
        $this->reset();

        //Set the status
        if (isset($config->status)) {
            $this->setStatus($config->status);
        }

        // Set the row data
        if (isset($config->data)) {
            $this->setData($config->data->toArray(), $this->isNew());
        }

        //Set the status message
        if (!empty($config->status_message)) {
            $this->setStatusMessage($config->status_message);
        }
	}

	/**
	 * Initializes the options for the object
	 *
	 * Called from {@link __construct()} as a first step of object instantiation.
	 *
	 * @param   KObjectConfig $config Configuration options
	 * @return void
	 */
	protected function _initialize(KObjectConfig $config)
	{
		$config->append(array(
			'table'	=> $this->getIdentifier()->name
		));

		parent::_initialize($config);
	}

	/**
     * Method to get a table object
     *
     * Function catches RuntimeException that are thrown for tables that
     * don't exist. If no table object can be created the function will return FALSE.
     *
     * @return KDatabaseTableAbstract
     */
    public function getTable()
    {
        if($this->_table !== false)
        {
            if(!($this->_table instanceof KDatabaseTableInterface))
		    {
		        //Make sure we have a table identifier
		        if(!($this->_table instanceof KObjectIdentifier)) {
		            $this->setTable($this->_table);
			    }

		        try {
		            $this->_table = $this->getObject($this->_table);
                } catch (RuntimeException $e) {
                    $this->_table = false;
                }
            }
        }

        return $this->_table;
    }

	/**
	 * Method to set a table object attached to the rowset
	 *
	 * @param	mixed	$table An object that implements KObjectInterface, KObjectIdentifier object
	 * 					or valid identifier string
	 * @throws	UnexpectedValueException	If the identifier is not a table identifier
	 * @return	KDatabaseRowsetAbstract
	 */
    public function setTable($table)
	{
		if(!($table instanceof KDatabaseTableInterface))
		{
			if(is_string($table) && strpos($table, '.') === false )
		    {
		        $identifier         = $this->getIdentifier()->toArray();
		        $identifier['path'] = array('database', 'table');
		        $identifier['name'] = KStringInflector::tableize($table);

                $identifier = $this->getIdentifier($identifier);
		    }
		    else  $identifier = $this->getIdentifier($table);

			if($identifier->path[1] != 'table') {
				throw new UnexpectedValueException('Identifier: '.$identifier.' is not a table identifier');
			}

			$table = $identifier;
		}

		$this->_table = $table;

		return $this;
	}

	/**
	 * Test the connected status of the row.
	 *
	 * @return	boolean	Returns TRUE if we have a reference to a live KDatabaseTableAbstract object.
	 */
    public function isConnected()
	{
	    return (bool) $this->getTable();
	}

	/**
	 * Load the row from the database using the data in the row
	 *
	 * @return object	If successful returns the row object, otherwise NULL
	 */
	public function load()
	{
		$result = null;

		if($this->isNew())
		{
            if($this->isConnected())
            {
                $data  = $this->getTable()->filter($this->getData(true), true);
		        $row   = $this->getTable()->select($data, KDatabase::FETCH_ROW);

		        // Set the data if the row was loaded successfully.
                if (!$row->isNew())
                {
                    $this->setData($row->getData(), false);
                    $this->_modified = array();

                    $this->setStatus(KDatabase::STATUS_LOADED);
                    $result = $this;
                }
            }
		}

		return $result;
	}

	/**
	 * Saves the row to the database.
	 *
	 * This performs an intelligent insert/update and reloads the properties
	 * with fresh data from the table on success.
	 *
	 * @return boolean	If successful return TRUE, otherwise FALSE
	 */
	public function save()
	{
	    $result = false;

	    if($this->isConnected())
	    {
	        if($this->isNew()) {
	            $result = $this->getTable()->insert($this);
		    } else {
		        $result = $this->getTable()->update($this);
		    }

	        if($result !== false)
	        {
	            // Filter out any extra columns.
	            if(((integer) $result) > 0) {
                    $this->_modified = array();
	            }
            }
	    }

		return (bool) $result;
    }

	/**
	 * Deletes the row form the database.
	 *
	 * @return boolean	If successful return TRUE, otherwise FALSE
	 */
	public function delete()
	{
		$result = false;

        if ($this->isConnected())
        {
            if (!$this->isNew()) {
                $result = $this->getTable()->delete($this);
            }
        }

		return (bool) $result;
	}

	/**
	 * Reset the row data using the defaults
	 *
	 * @return boolean	If successful return TRUE, otherwise FALSE
	 */
	public function reset()
	{
		$result = parent::reset();

		if($this->isConnected())
		{
	        if($this->_data = $this->getTable()->getDefaults()) {
		        $result = true;
		    }
		}

		return $result;
	}

    /**
     * Unset a row field
     *
     * This function will reset required column to their default value, not required fields will be unset.
     *
     * @param    string  $column The column name.
     * @return   void
     */
    public function offsetUnset($column)
    {
        if ($this->isConnected())
        {
            $field = $this->getTable()->getColumn($column);

            if (isset($field) && $field->required) {
                parent::offsetSet($this->_data[$column], $field->default);
            } else {
                parent::offsetUnset($column);
            }
        }
    }

	/**
	 * Search the mixin method map and call the method or trigger an error
	 *
	 * This functions overloads KDatabaseRowAbstract::__call and implements
	 * a just in time mixin strategy. Available table behaviors are only mixed
	 * when needed.
	 *
	 * @param  string 	$method    The function name
	 * @param  array  	$arguments The function arguments
	 * @throws BadMethodCallException 	If method could not be found
	 * @return mixed The result of the function
	 */
	public function __call($method, $arguments)
	{
        if ($this->isConnected())
        {
            $parts = KStringInflector::explode($method);

            if($parts[0] == 'is' && isset($parts[1]))
            {
                if(!isset($this->_mixed_methods[$method]))
                {
                    //Lazy mix behaviors
                    $behavior = strtolower($parts[1]);

                    if ($this->getTable()->hasBehavior($behavior))
                    {
                        $this->mixin($this->getTable()->getBehavior($behavior));
                        return true;
                    }

                    return false;
                }

                return true;
            }
        }

        return parent::__call($method, $arguments);
	}
}
