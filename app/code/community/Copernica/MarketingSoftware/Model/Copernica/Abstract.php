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
     *  @var String
     */
    protected $direction = 'copernica';

    /**
     *  Set the direction for this synchronisation
     *  @param  String
     *  @return Copernica_MarketingSoftware_Model_Copernica_Orderitem_Subprofile
     */
    public function setDirection($direction)
    {
        $this->direction = $direction;
        return $this;
    }

    /**
     *  Return the identifier for this profile
     *  @return string
     */
    abstract public function id();

    /**
     *  Retrieve the data for this object
     *  @return array
     */
    abstract protected function _data();

    /**
     *  Get linked fields
     *  @return array
     */
    abstract public function linkedFields();

    /**
     *  Get the required fields
     *  @return array
     */
    abstract public function requiredFields();

    /**
     *  Check if offset exists in array
     *  @param  mixed
     *  @return boolean
     */
    public function offsetExists($offset)
    {
        // Get the data from this profile
        $data = $this->toArray();

        // Return the data based on whether it exists
        return isset($data[$offset]);
    }

    /**
     *  Get offset in array
     *  @param  mixed
     *  @return mixed
     */
    public function offsetGet($offset)
    {
        // Get the data from this profile
        $data = $this->toArray();

        // Return the data based on whether it exists
        return isset($data[$offset]) ? $data[$offset] : null;
    }

    /**
     *  Convert the data to an array for usage in SOAP
     *  
     *  @return array
     */
    public function toArray()
    {
        // Get the data
        $data = $this->_data();

        // this data is meant for magento, return the data as is
        if ($this->direction == 'magento') return $data;

        // construct an array which contains the required base record
        $returndata = array();

        // check the required fields
        foreach ($this->requiredFields() as $field) $returndata[$field] = $data[$field];

        // iterate over the linked fields
        foreach ($this->linkedFields() as $magentoField => $copernicaField)
        {
            // Not linked to a field, skip
            if (empty($copernicaField) || !isset($data[$magentoField])) continue;

            // Append it to the returned array
            $returndata[$copernicaField] = $data[$magentoField];
        }

        // Return the mapped data
        return $returndata;
    }

    /**
     *  Set entry in offset of array
     *  @deprecated
     *  @param  mixed
     *  @param  mixed
     */
    public function offsetSet($offset, $value)
    {
        return;
    }

    /**
     *  Remove entry by offset in array
     *  @deprecated
     *  @param  mixed
     */
    public function offsetUnset($offset)
    {
        return;
    }
}