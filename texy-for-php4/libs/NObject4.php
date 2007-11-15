<?php

/**
 * Texy! - web text markup-language (for PHP 4)
 * --------------------------------------------
 *
 * Copyright (c) 2004, 2007 David Grudl aka -dgx- (http://www.dgx.cz)
 *
 * This source file is subject to the GNU GPL license that is bundled
 * with this package in the file license.txt.
 *
 * For more information please see http://texy.info/
 *
 * @copyright  Copyright (c) 2004, 2007 David Grudl
 * @license    GNU GENERAL PUBLIC LICENSE version 2 or 3
 * @link       http://texy.info/
 * @package    Texy
 */



/**
 * Compatibility with PHP < 5
 *
 * Example: $obj = clone ($dolly)
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2007 David Grudl
 * @link       http://latrine.dgx.cz/how-to-emulate-php5-object-model-in-php4
 */
if (PHP_VERSION < 5) {
    eval('
    function clone($obj)
    {
        foreach($obj as $key => $value) {
            $obj->$key = & $value;               // reference to new variable
            $GLOBALS[\'$$HIDDEN$$\'][] = & $value; // and generate reference
            unset($value);
        }

        // call $obj->__clone()
        if (is_callable(array(&$obj, \'__clone\'))) $obj->__clone();

        return $obj;
    }

    function throw($e)
    {
        trigger_error($e->message, E_USER_ERROR);
        die();
    }
    ');

    class Exception
    {
        var $message;
        var $code;
        var $trace;

        function Exception($message = NULL, $code=0)
        {
            $this->message = $message;
            $this->code = $code;
            $this->trace = debug_backtrace();
        }
    }
}


/**
 * NObject (for PHP4) is the ultimate ancestor of all instantiable classes.
 *
 * It defines some handful methods and enhances object core of PHP:
 *   - access to undeclared members throws exceptions
 *   - ability to add new methods to class (extension methods)
 *   - support for conventional properties with getters and setters
 *
 * Properties is a syntactic sugar which allows access public getter and setter
 * methods as normal object variables. A property is defined by a getter method
 * and optional setter method (no setter method means read-only property).
 * <code>
 * $val = $obj->Label;     // equivalent to $val = $obj->getLabel();
 * $obj->Label = 'Nette';  // equivalent to $obj->setLabel('Nette');
 * </code>
 * Property names are case-sensitive, and they are written in the PascalCaps,
 * in contrast to camelCaps used by normal members.
 *
 * Adding method to class (i.e. to all instances) works similar to JavaScript
 * prototype property. The syntax for adding a new method is:
 * <code>
 * function MyClass_prototype_newMethod(MyClass $obj, $arg, ...) { ... }
 * $obj = new MyClass;
 * $obj->newMethod($x); // equivalent to MyClass_prototype_newMethod($obj, $x);
 * </code>
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2007 David Grudl
 * @license    http://php7.org/nette/license  Nette license
 * @link       http://php7.org/nette/
 * @package    Nette
 */
class NObject4 /* abstract  */
{

    function __construct()  /* PHP 5 constructor */
    {}



    function NObject4()  /* PHP 4 constructor */
    {
        // generate references (see http://latrine.dgx.cz/how-to-emulate-php5-object-model-in-php4)
        foreach ($this as $key => $foo) $GLOBALS['$$HIDDEN$$'][] = & $this->$key;

        $args = func_get_args();
        call_user_func_array(array(&$this, '__construct'), $args);
    }



    /**
     * Returns the name of the class of this object
     *
     * @return string
     */
    function getClass()
    {
        return get_class($this);
    }



    /**
     * Access to reflection
     *
     * @return ReflectionObject
     */
    function getReflection()
    {
        return new ReflectionObject($this);
    }



    /**
     * Call to undefined method
     *
     * @param string  method name
     * @param array   arguments
     * @return mixed
     * @throws BadMethodCallException
     */
    function __call($name, $args)
    {
        if ($name === '') {
            throw (new BadMethodCallException("Call to method without name"));
        }

        // object prototypes support Class__method()
        // (or use class Class__method { static function ... } with autoloading?)
        $cl = $class = get_class($this);
        do {
            if (function_exists($nm = $cl . '_prototype_' . $name)) {
                array_unshift($args, $this);
                return call_user_func_array($nm, $args);
            }
        } while ($cl = get_parent_class($cl));

        throw (new BadMethodCallException("Call to undefined method $class::$name()"));
    }



    /**
	 * Returns property value. Do not call directly.
     *
     * @param string  property name
	 * @return mixed  property value or the event handler list
	 * @throws LogicException if the property is not defined.
	 */
	function &__get($name)
	{
        if ($name === '') {
            throw (new LogicException("Cannot read an property without name"));
        }

        // property getter support
        $class = get_class($this);
        $m = 'get' . $name;
        if (self::isCallable($class, $m)) {
            // ampersands:
            // - using &__get() because declaration should be forward compatible (e.g. with NHtml)
            // - not using &$this->$m because user could bypass property setter by: $x = & $obj->property; $x = 'new value';
            $val = $this->$m();
            return $val;

        } else {
            throw (new LogicException("Cannot read an undeclared property $class::\$$name"));
        }
	}



	/**
	 * Sets value of a property. Do not call directly.
     *
	 * @param string  property name
	 * @param mixed   property value
     * @return void
     * @throws LogicException if the property is not defined or is read-only
	 */
	function __set($name, $value)
	{
        if ($name === '') {
            throw (new LogicException('Cannot assign to an property without name'));
        }

        // property setter support
        $class = get_class($this);
        if (self::isCallable($class, 'get' . $name)) {
            $m = 'set' . $name;
            if (self::isCallable($class, $m)) {
                $this->$m($value);

            } else {
                throw (new LogicException("Cannot assign to a read-only property $class::\$$name"));
            }

        } else {
            throw (new LogicException("Cannot assign to an undeclared property $class::\$$name"));
        }
	}



	/**
	 * Is property defined?
     *
	 * @param string  property name
	 * @return bool
	 */
    function __isset($name)
	{
    	return $name !== '' && self::isCallable(get_class($this), 'get' . $name);
	}



    /**
     * Access to undeclared property
     *
     * @param string  property name
	 * @return void
     * @throws LogicException
     */
    function __unset($name)
    {
        $class = get_class($this);
        throw (new LogicException("Cannot unset an property $class::\$$name"));
    }



    /**
	 * Does method exist? Case sensitive, filters protected & private, doesn't recognize static methods (works good since 5.0.4)
     *
	 * @param string  class name
     * @param string  method name
	 * @return bool
	 */
    function isCallable($c, $m)
    {
        static $cache;
        if (!isset($cache[$c])) {
            $cache[$c] = array_flip(get_class_methods($c));
        }
        return isset($cache[$c][$m]);
    }

}



/**
 * NClass (for PHP4) is the ultimate ancestor of all uninstantiable classes.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2007 David Grudl
 * @license    http://php7.org/nette/license  Nette license
 * @link       http://php7.org/nette/
 * @package    Nette
 */
class NClass4
{

    function __construct()
    {
        throw (new LogicException("Cannot instantiate static class " . get_class($this)));
    }

}
