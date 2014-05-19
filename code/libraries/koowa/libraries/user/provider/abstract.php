<?php
/**
 * Koowa Framework - http://developer.joomlatools.com/koowa
 *
 * @copyright	Copyright (C) 2007 - 2013 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		http://github.com/joomlatools/koowa for the canonical source repository
 */

/**
 * Abstract User Provider
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Library\User
 */
class KUserProviderAbstract extends KObject implements KUserProviderInterface
{
    /**
     * The list of users
     *
     * @var array
     */
    protected $_users = array();

    /**
     * Constructor
     *
     * The user array is a hash where the keys are user identifier and the values are an array of attributes:
     * 'password', 'enabled', and 'roles' etc. The user identifiers should be unique.
     *
     * @param   KObjectConfig $config  An optional ObjectConfig object with configuration options
     * @return  KUserProviderAbstract
     */
    public function __construct(KObjectConfig $config)
    {
        parent::__construct($config);

        //Create the users
        foreach($config->users as $identifier => $data) {
            $this->_users[$identifier] = $this->create($data);
        }
    }

    /**
     * Initializes the options for the object
     *
     * Called from {@link __construct()} as a first step of object instantiation
     *
     * @param   KObjectConfig $object An optional ObjectConfig object with configuration options
     * @return  void
     */
    protected function _initialize(KObjectConfig $config)
    {
        $config->append(array(
            'users' => array(),
        ));

        parent::_initialize($config);
    }

    /**
     * Loads the user for the given user identifier
     *
     * @param string $identifier A unique user identifier, (i.e a username or email address)
     * @param bool  $refresh     If TRUE and the user has already been loaded it will be re-loaded.
     * @return KUserInterface Returns a UserInterface object
     */
    public function load($identifier, $refresh = false)
    {
        //Fetch a user from the backend
        if($refresh || !isset($this->_users[$identifier]))
        {
            $user = $this->fetch($identifier);
            $this->_users[$identifier] = $user;
        }

        return  $this->_users[$identifier];
    }

    /**
     * Fetch the user for the given user identifier from the backend
     *
     * @param string $identifier A unique user identifier, (i.e a username or email address)
     * @return KUserInterface|null Returns a UserInterface object or NULL if the user could not be found.
     */
    public function fetch($identifier)
    {
        $data = array(
            'id'         => $identifier,
            'authentic'  => false
        );

        return $this->create($data);
    }

    /**
     * Create a user object
     *
     * @param array $data An associative array of user data
     * @return KUserInterface     Returns a UserInterface object
     */
    public function create($data)
    {
        $user = $this->getObject('user.default', array('data' => $data));
        return $user;
    }
}