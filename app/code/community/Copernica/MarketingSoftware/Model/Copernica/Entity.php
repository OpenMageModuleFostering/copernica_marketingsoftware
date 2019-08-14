<?php
/**
 * Copernica Marketing Software 
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0).
 * It is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you are unable to obtain a copy of the license through the 
 * world-wide-web, please send an email to copernica@support.cream.nl 
 * so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this software 
 * to newer versions in the future. If you wish to customize this module 
 * for your needs please refer to http://www.magento.com/ for more 
 * information.
 *
 * @category     Copernica
 * @package      Copernica_MarketingSoftware
 * @copyright    Copyright (c) 2011-2012 Copernica & Cream. (http://docs.cream.nl/)
 * @license      http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 *  This is a base class for all bridge classes between Magento and Copernica
 */
abstract class Copernica_MarketingSoftware_Model_Copernica_Entity
{
    /**
     *  Cached data.
     *  @var array
     */
    protected $data = array();

    /**
     *  This cosntructor has to be overriden inside child.
     *  @param int  Id of magento entity
     */
    protected function __construct($itemId)
    {
        // override in child class
    }

    /**
     *  This is a factory method that will produce proper entities.
     *  @param  string
     *  @param  string|int
     *  @return Copernica_MarketingSoftware_Model_Copernica_Entity
     */
    public function create($itemName, $itemId)
    {
        // construct proper child class
        $childClass = 'Copernica_MarketingSoftware_Model_Copernica_Entity_'.ucfirst($itemName);

        // check if child class exists
        if (!class_exists($childClass)) return null;

        // create new child class
        return new $childClass($itemId);
    }

    /**
     *  @param string method name
     */
    public function __call($methodName, $arguments)
    {
        // check if we want to get something
        if (substr($methodName, 0, 3) != 'get') return null;

        // get the name of the property
        $property = substr($methodName, 3);

        // cause some really old PHP can be used...
        $property{0} = strtolower($property{0});

        // check if property was alredy fetched
        if (array_key_exists($property, $this->data)) return $this->data[$property];

        // construct fetch method name
        $fetchMethod = 'fetch'.ucfirst($property);

        // try to fetch data and store it inside registry
        if (method_exists($this, $fetchMethod) && !is_null($value = $this->$fetchMethod())) return $this->data[$property] = $value;

        // we didn't found anything
        return null;
    }
}