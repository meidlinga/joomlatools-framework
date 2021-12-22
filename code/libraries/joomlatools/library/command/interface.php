<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2007 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

/**
 * Command Context Interface
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Library\Command
 */
interface KCommandInterface
{
    /**
     * Get the event name
     *
     * @return string	The event name
     */
    public function getName();

    /**
     * Set the event name
     *
     * @param string $name The event name
     * @return KCommandInterface
     */
    public function setName($name);

    /**
     * Get the command subject
     *
     * @return mixed The command subject
     */
    public function getSubject();

    /**
     * Set the command subject
     *
     * @param  mixed $subject The command subject
     * @return KCommandInterface
     */
    public function setSubject($subject);

    /**
     * Get the command result
     *
     * @return mixed The command result
     */
    public function getResult();

    /**
     * Set the command result
     *
     * @param mixed $subject The command result
     * @return KCommand
     */
    public function setResult($result);

    /**
     * Set attributes
     *
     * Overwrites existing attributes
     *
     * @param  array|Traversable $attributes
     * @throws InvalidArgumentException If the attributes are not an array or are not traversable.
     * @return KCommandInterface
     */
    public function setAttributes($attributes);

    /**
     * Get all arguments
     *
     * @return array
     */
    public function getAttributes();

    /**
     * Get an attribute
     *
     * If the attribute does not exist, the $default value will be returned.
     *
     * @param  string $name The attribute name
     * @param  mixed $default
     * @return mixed
     */
    public function getAttribute($name, $default = null);

    /**
     * Set an attribute
     *
     * @param  string $name The attribute
     * @param  mixed $value
     * @return KCommandInterface
     */
    public function setAttribute($name, $value);
}
