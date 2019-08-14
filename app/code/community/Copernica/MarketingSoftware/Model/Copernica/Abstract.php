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
 *  An object to wrap the Copernica profile
 */
abstract class Copernica_MarketingSoftware_Model_Copernica_Abstract implements ArrayAccess
{
    /**
     *  To where data will be saved? Posible values are 'copernica' and 'magento'
     *  
     *  @var	String
     */
    protected $_direction = 'copernica';

    /**
     *  Set the direction for this synchronisation
     *  
     *  @param	string	$direction
     *  @return	Copernica_MarketingSoftware_Model_Copernica_Orderitem_Subprofile
     */
    public function setDirection($direction)
    {
        $this->_direction = $direction;
        
        return $this;
    }

    /**
     *  Return the identifier for this profile
     *  
     *  @return string
     */
    abstract public function id();

    /**
     *  Retrieve the data for this object
     *  
     *  @return array
     */
    abstract protected function _data();

    /**
     *  Get linked fields
     *  
     *  @return array
     */
    abstract public function linkedFields();

    /**
     *  Get the required fields
     *  
     *  @return array
     */
    abstract public function requiredFields();

    /**
     *  Check if offset exists in array
     *  
     *  @param	mixed	$offset
     *  @return boolean
     */
    public function offsetExists($offset)
    {
        $data = $this->toArray();

        return isset($data[$offset]);
    }

    /**
     *  Get offset in array
     *  
     *  @param	mixed	$offset
     *  @return mixed
     */
    public function offsetGet($offset)
    {
        $data = $this->toArray();

        return isset($data[$offset]) ? $data[$offset] : null;
    }

    /**
     *  Convert the data to an array for usage in SOAP
     *  
     *  @return array
     */
    public function toArray()
    {
        $data = $this->_data();

        if ($this->_direction == 'magento') {
        	return $data;
        }

        $returndata = array();

        foreach ($this->requiredFields() as $field) {
        	$returndata[$field] = $data[$field];
        }

        foreach ($this->linkedFields() as $magentoField => $copernicaField) {
            if (empty($copernicaField) || !isset($data[$magentoField])) {
            	continue;
            }

            $returndata[$copernicaField] = $data[$magentoField];
        }

        return $returndata;
    }

    /**
     *  Set entry in offset of array
     *  
     *  @deprecated
     *  @param	mixed	$offset
     *  @param  mixed	$value
     */
    public function offsetSet($offset, $value)
    {
        return;
    }

    /**
     *  Remove entry by offset in array
     *  
     *  @deprecated
     *  @param	mixed	$offset
     */
    public function offsetUnset($offset)
    {
        return;
    }
}