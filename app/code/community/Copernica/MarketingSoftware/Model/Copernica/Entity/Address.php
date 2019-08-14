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

class Copernica_MarketingSoftware_Model_Copernica_Entity_Address extends Copernica_MarketingSoftware_Model_Copernica_Entity
{
    /**
     *  Magento address model
     *  
     *  @var	Mage_Customer_Model_Address
     */
    protected $_address = null;

    /**
     *  Due to legacy code this method is complicated. 
     *  
     *  @return string
     */
    public function fetchId()
    {
        switch (get_class($this->_address)) {
            case 'Mage_Customer_Model_Address': 
            	return 'ca_'.$this->_address->getId();
            	
            case 'Mage_Sales_Model_Order_Address':
                if ($customerAddressId = $this->_address->getCustomerAddressId()) {
                	return 'ca_'.$customerAddressId;
                }
                else {
                	return 'oa_'.$this->_address->getId();
                }
                
            case 'Mage_Sales_Model_Quote_Address':
                if ($customerAddressId = $this->_address->getCustomerAddressId()) {
                	return 'ca_'.$customerAddressId;
                }
                else {
                	return 'ca_'.$customerAddressId;
                }
                
            default: 
            	return $this->_address->getId();
        }
    }

    /**
     *  Fetch email
     *  
     *  @return	string
     */
    public function fetchEmail()
    {
        if ($email = $this->_address->getEmail()) {
        	return $email;
        }

        if (is_object($order = $this->_address->getOrder()) && $email = $order->getCustomerEmail()) {
        	return $email;
        }

        if (is_object($quote = $this->_address->getQuote()) && $email = $quote->getCustomerEmail()) {
        	return $email;
        }

        if (is_object($customer = $this->_address->getCustomer()) && $email = $customer->getEmail()) {
        	return $email;
        }

        return '';
    }

    /**
     *  Fetch name object for this address
     *  
     *  @return	Copernica_MarketingSoftware_Model_Abstraction_Name
     */
    public function fetchName()
    {
        return Mage::getModel('marketingsoftware/abstraction_name')->setOriginal($this->_address);
    }    

    /**
     *  Fetch firstname
     *  
     *  @return	string
     */
    public function fetchFirstname()
    {
        return $this->getName()->firstname();
    }

    /**
     *  Fetch middlename
     *  
     *  @return	string
     */
    public function fetchMiddlename()
    {
        return $this->getName()->middlename();
    }

    /**
     *  Fetch lastname
     *  
     *  @return	string
     */
    public function fetchLastname()
    {
        return $this->getName()->lastname();
    }

    /**
     *  Fetch prefix
     *  
     *  @return	string
     */
    public function fetchPrefix()
    {
        return $this->getName()->prefix();
    }

    /**
     *  Fetch street
     *  
     *  @return	string
     */
    public function fetchStreet()
    {
        return $this->_address->getStreetFull();
    }

    /**
     *  Fetch city
     *  
     *  @return	string
     */
    public function fetchCity()
    {
        return $this->_address->getCity();
    }

    /**
     *  Fetch postal code
     *   
     *  @return	string
     */
    public function fetchZipcode()
    {
        return $this->_address->getPostcode();
    }

    /**
     *  Fetch state
     *  
     *  @return	string
     */
    public function fetchState()
    {
        return $this->_address->getRegion();
    }

    /**
     *  Fetch country
     *  
     *  @return	string
     */
    public function fetchCountryId()
    {
        return $this->_address->getCountryId();
    }

    /**
     *  Fetch company
     *  
     *  @return	string
     */
    public function fetchCompany()
    {
        return $this->_address->getCompany();
    }

    /**
     *  Fetch telephone
     *  
     *  @return	string
     */
    public function fetchTelephone()
    {
        return $this->_address->getTelephone();
    }

    /**
     *  Fetch fax
     *  
     *  @return	string
     */
    public function fetchFax()
    {
        return $this->_address->getFax();
    }

    /**
     *  Get REST address entity
     *  
     *  @return	Copernica_MarketingSoftware_Model_Rest_Address
     */
    public function getRestAddress()
    {
    	$restAddress = Mage::getModel('marketingsoftware/rest_address');
    	$restAddress->setAddressEntity($this);
    	 
    	return $restAddress;
    }
    
    
    /**
     *  Set address entity
     *  It is expecting to receive one of magento address types. 
     *
     *  @param	Mage_Customer_Model_Address|Mage_Sales_Model_Order_Address|Mage_Sales_Model_Quote_Address	$address
     */
    public function setAddress($address) 
    {
    	$this->_address = $address;
    }
}