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
 *  A wrapper object around a name, note this is not an Magento object
 */
class Copernica_MarketingSoftware_Model_Abstraction_Name implements Serializable
{
    /**
     * Predefine the internal fields
     */
    protected $firstname;
    protected $prefix;
    protected $middlename;
    protected $lastname;

    /**
     *  Sets the original model
     *  @param      Mage_Customer_Model_Customer|Mage_Customer_Model_Customer_Address|Mage_Sales_Model_Order_Address|Mage_Sales_Model_Quote_Address|Mage_Customer_Model_Address
     *  @return     Copernica_MarketingSoftware_Model_Abstraction_Name
     */
    public function setOriginal($original)
    {
        $this->firstname = $original->getFirstname();
        $this->prefix = $original->getPrefix();
        $this->middlename = $original->getMiddlename();
        $this->lastname = $original->getLastname();
        
        return $this;
    }

    /**
     *  Return the firstname of the customer
     *  @return     string
     */
    public function firstname()
    {
        return $this->firstname;
    }

    /**
     *  Return the prefix of the customer
     *  NOTE: the prefix field is not displayed by default
     *  @return     string
     */
    public function prefix()
    {
        return $this->prefix;
    }

    /**
     *  Return the middlename of the customer
     *  NOTE: the middlename field is not displayed by default
     *  @return     string
     */
    public function middlename()
    {
        return $this->middlename;
    }

    /**
     *  Return the lastname of the customer
     *  @return     string
     */
    public function lastname()
    {
        return $this->lastname;
    }

    /**
     *  Serialize the object
     *  @return     string
     */
    public function serialize()
    {
        // serialize the data
        return serialize(array(
            $this->firstname(),
            $this->prefix(),
            $this->middlename(),
            $this->lastname(),
        ));
    }

    /**
     *  Unserialize the object
     *  @param      string
     *  @return     Copernica_MarketingSoftware_Model_Abstraction_Name
     */
    public function unserialize($string)
    {
        list(
            $this->firstname,
            $this->prefix,
            $this->middlename,
            $this->lastname
        ) = unserialize($string);
    }
}

