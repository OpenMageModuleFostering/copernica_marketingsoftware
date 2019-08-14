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
 *  A wrapper object around an Address
 */
class Copernica_MarketingSoftware_Model_Abstraction_Address implements Serializable
{
    /**
     *  The original object
     *  
     *  @todo	Not used???
     *  @var	Mage_Sales_Model_Order_Address|Mage_Sales_Model_Quote_Address|Mage_Customer_Model_Address
     */
    protected $_original;

    /** 
     * Predefine the internal fields
     */ 
    protected $_id;
    protected $_type;
    protected $_name;
    protected $_email;
    protected $_street;
    protected $_city;
    protected $_zipcode;
    protected $_state;
    protected $_countryId;
    protected $_telephone;
    protected $_fax;
    protected $_company;
    protected $_customerId = null;
    
    /**
     *  Sets the original model
     *  
     *  @param	Mage_Sales_Model_Order_Address|Mage_Sales_Model_Quote_Address|Mage_Customer_Model_Address	$original
     *  @return	Copernica_MarketingSoftware_Model_Abstraction_Address
     */
    public function setOriginal($original)
    {
        $this->_name = Mage::getModel('marketingsoftware/abstraction_name')->setOriginal($original);
        $this->_street = $original->getStreetFull();
        $this->_city = $original->getCity();
        $this->_zipcode = $original->getPostcode();
        $this->_state = $original->getRegion();
        $this->_company = $original->getCompany();
        $this->_countryId = $original->getCountryId();
        $this->_telephone = $original->getTelephone();
        $this->_fax = $original->getFax();
        
        if ($type = $original->getAddressType()) {
            $this->_type = array($type);
        } else {
            $types = array();
            
            if ($customer = $original->getCustomer()) {
                $id = $original->getId();
                
                if ($customer->getData('default_billing') == $id) {
                    $types[] = 'billing';
                }
                
                if ($customer->getData('default_shipping') == $id) {
                    $types[] = 'shipping';
                }
            }
            $this->_type = $types;
        }
        
        if ($customerId = $original->getCustomerId()) {
            $this->_customerId = $original->getCustomerId();
        } elseif ($customer = $original->getCustomer()) {
            $this->_customerId = $customer->getId();
        } 
        
        $id = $original->getId();
        
        switch(get_class($original)) {
            case "Mage_Sales_Model_Order_Address":
                if ($cid = $original->getCustomerAddressId()) {
                    $this->_id = 'ca_'.$cid;
                } else {
                    $this->_id = 'oa_'.$id;
                }
                break;
            case "Mage_Sales_Model_Quote_Address":
                if ($cid = $original->getCustomerAddressId()) {
                    $this->_id = 'ca_'.$cid;
                } else {
                    $this->_id = 'qa_'.$id;
                }
                break;
            case "Mage_Customer_Model_Address": 
                $this->_id = 'ca_'.$id;
                break;
            default: 
                $this->_id = $id;
        }

        if ($email = $original->getEmail()) {
            $this->_email = $email;
        } elseif (is_object($order = $original->getOrder()) && $customerEmail = $order->getCustomerEmail()) {
            $this->_email = $customerEmail;
        } elseif (is_object($quote = $original->getQuote()) && $customerEmail = $quote->getCustomerEmail()) { 
            $this->_email = $customerEmail;
        }
        
        return $this;
    }

    /**
     *  Return the type of this address
     *  
     *  @return	array
     */
    public function type()
    {
        return $this->_type;
    }
    
    /**
     *  The customer may return null
     *  
     *  @return	Copernica_MarketingSoftware_Model_Abstraction_Customer|null
     */
    public function customer()
    {
        if ($this->_customerId) {
            return Mage::getModel('marketingsoftware/abstraction_customer')->loadCustomer($this->_customerId);
        } else {
            return null;
        }
    }
    
    /**
     *  We want to return a unique id, but to do this we need to append
     *  a prefix based on the type of address
     *  
     *  @return string
     */
    public function id()
    {
        return $this->_id;
    }
    
    /**
     *  Return the name belonging to this address
     *  
     *  @return	Copernica_MarketingSoftware_Model_Abstraction_Name
     */
    public function name()
    {
        return $this->_name;
    }

    /**
     *  Return the e-mail address of this address
     *  
     *  @return	string
     */
    public function email()
    {
        return $this->_email;
    }

    /**
     *  Return the street of this addresses
     *  
     *  @return	string
     */
    public function street()
    {
        return $this->_street;
    }

    /**
     *  Get the city of this addresses
     *  
     *  @return	string
     */
    public function city()
    {
        return $this->_city;
    }

    /**
     *  Get the zipcode of this addresses
     *  
     *  @return	string
     */
    public function zipcode()
    {
        return $this->_zipcode;
    }

    /**
     *  Get the state of this addresses
     *  
     *  @return	string
     */
    public function state()
    {
        return $this->_state;
    }

    /**
     *  Get the country id of this addresses
     *  
     *  @return	string
     */
    public function countryId()
    {
        return $this->_countryId;
    }

    /**
     *  Get the telephone number of this addresses
     *  
     *  @return	string
     */
    public function telephone()
    {
        return $this->_telephone;
    }

    /**
     *  Get the fax number of this addresses
     *  
     *  @return	string
     */
    public function fax()
    {
        return $this->_fax;
    }

    /**
     *  Get the company of this address
     *  
     *  @return	string
     */
    public function company()
    {
        return $this->_company;
    }

    /**
     *  Serialize the object
     *  
     *  @return	string
     */
    public function serialize()
    {
        return serialize(array(
            $this->id(),
            $this->type(),
            $this->name(),
            $this->email(),
            $this->street(),
            $this->city(),
            $this->zipcode(),
            $this->state(),
            $this->countryId(),
            $this->telephone(),
            $this->fax(),
            $this->company(),
            is_object($customer = $this->customer()) ? $customer->id() : null,
        ));
    }

    /**
     *  Unserialize the object
     *  
     *  @param	string	$string
     *  @return	Copernica_MarketingSoftware_Model_Abstraction_Address
     */
    public function unserialize($string)
    {
        list(
            $this->_id,
            $this->_type,
            $this->_name,
            $this->_email,
            $this->_street,
            $this->_city,
            $this->_zipcode,
            $this->_state,
            $this->_countryId,
            $this->_telephone,
            $this->_fax,
            $this->_company,
            $this->_customerId
        ) = unserialize($string);
        
        return $this;
    }
}