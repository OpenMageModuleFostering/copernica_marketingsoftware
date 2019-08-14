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
 * @copyright    Copyright (c) 2011-2015 Copernica & Cream. (http://docs.cream.nl/)
 * @license      http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 *  This is a base class for all bridge classes between Magento and Copernica
 */
abstract class Copernica_MarketingSoftware_Model_Copernica_Entity
{
    /**
     *  Cached data.
     *  
     *  @var	array
     */
    protected $_data = array();

    /**
     *  This is a factory method that will produce proper entities.
     *  
     *  @todo	Verify whether it is actually needed
     *  @param  string	$itemName
     *  @return Copernica_MarketingSoftware_Model_Copernica_Entity
     */
    public function create($itemName)
    {
    	$modelName = 'marketingsoftware/copernica_entity_'. $itemName;
    	
    	if (!class_exists(Mage::getConfig()->getModelClassName($modelName))) {
    		return null;
    	}    	
    	
    	return Mage::getModel($modelName);    	
    }

    /**
     * 	@todo	param arguments
     *  @param	string	$methodName
     */
    public function __call($methodName, $arguments)
    {
        if (substr($methodName, 0, 3) != 'get') {
        	return null;
        }

        $property = substr($methodName, 3);
        $property{0} = strtolower($property{0});

        if (array_key_exists($property, $this->_data)) {
        	return $this->_data[$property];
        }

        $fetchMethod = 'fetch'.ucfirst($property);

        if (method_exists($this, $fetchMethod) && !is_null($value = $this->$fetchMethod())) {
        	return $this->_data[$property] = $value;
        }

        return null;
    }
}
