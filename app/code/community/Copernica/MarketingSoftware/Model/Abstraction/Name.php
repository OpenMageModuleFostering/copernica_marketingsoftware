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
 *  A wrapper object around a name, note this is not an Magento object
 */
class Copernica_MarketingSoftware_Model_Abstraction_Name implements Serializable
{
    /**
     * Predefine the internal fields
     */
    protected $_firstname;
    protected $_prefix;
    protected $_middlename;
    protected $_lastname;

    /**
     *  Sets the original model
     *  
     *  @param	Mage_Customer_Model_Customer|Mage_Customer_Model_Customer_Address|Mage_Sales_Model_Order_Address|Mage_Sales_Model_Quote_Address|Mage_Customer_Model_Address
     *  @return	Copernica_MarketingSoftware_Model_Abstraction_Name
     */
    public function setOriginal($original)
    {
        $this->_firstname = $original->getFirstname();
        $this->_prefix = $original->getPrefix();
        $this->_middlename = $original->getMiddlename();
        $this->_lastname = $original->getLastname();
        
        return $this;
    }

    /**
     *  Return the firstname of the customer
     *  
     *  @return	string
     */
    public function firstname()
    {
        return $this->_firstname;
    }

    /**
     *  Return the prefix of the customer
     *  NOTE: the prefix field is not displayed by default
     *  
     *  @return	string
     */
    public function prefix()
    {
        return $this->_prefix;
    }

    /**
     *  Return the middlename of the customer
     *  NOTE: the middlename field is not displayed by default
     *  
     *  @return	string
     */
    public function middlename()
    {
        return $this->_middlename;
    }

    /**
     *  Return the lastname of the customer
     *  
     *  @return	string
     */
    public function lastname()
    {
        return $this->_lastname;
    }

    /**
     *  Serialize the object
     *  
     *  @return	string
     */
    public function serialize()
    {
        return serialize(array(
            $this->firstname(),
            $this->prefix(),
            $this->middlename(),
            $this->lastname(),
        ));
    }

    /**
     *  Unserialize the object
     *  
     *  @param	string	$string
     *  @return	Copernica_MarketingSoftware_Model_Abstraction_Name
     */
    public function unserialize($string)
    {
        list(
            $this->_firstname,
            $this->_prefix,
            $this->_middlename,
            $this->_lastname
        ) = unserialize($string);
        
        return $this;
    }
}

