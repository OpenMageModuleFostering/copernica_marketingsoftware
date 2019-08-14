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
 *  A wrapper object around an Address
 */
class Copernica_MarketingSoftware_Model_Abstraction_Address implements Serializable
{
    /**
     *  The original object
     *  @param      Mage_Sales_Model_Order_Address|Mage_Sales_Model_Quote_Address|Mage_Customer_Model_Address
     */
    protected $original;

    /** 
     * Predefine the internal fields
     */ 
    protected $id;
    protected $type;
    protected $name;
    protected $email;
    protected $street;
    protected $city;
    protected $zipcode;
    protected $state;
    protected $countryCode;
    protected $telephone;
    protected $fax;
    protected $company;
    protected $customerId = null;
    
    /**
     *  Sets the original model
     *  @param      Mage_Sales_Model_Order_Address|Mage_Sales_Model_Quote_Address|Mage_Customer_Model_Address $original
     *  @return     Copernica_MarketingSoftware_Model_Abstraction_Address
     */
    public function setOriginal($original)
    {
        $this->name = Mage::getModel('marketingsoftware/abstraction_name')->setOriginal($original);
        $this->street = $original->getStreetFull();
        $this->city = $original->getCity();
        $this->zipcode = $original->getPostcode();
        $this->state = $original->getRegion();
        $this->company = $original->getCompany();
        $this->countryCode = $original->getCountryId();
        $this->telephone = $original->getTelephone();
        $this->fax = $original->getFax();
        
		if ($type = $original->getAddressType()) {
        	$this->type = array($type);
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
        	$this->type = $types;
        }
        
       	//the order quote address model only returns a customer if it exists
       	if ($customerId = $original->getCustomerId()) {
       		$this->customerId = $original->getCustomerId();
        } elseif ($customer = $original->getCustomer()) {
        	$this->customerId = $customer->getId();
        } 
        
       	// Get the normal identifier
       	$id = $original->getId();
        
       	// switch depending on the type
       	switch(get_class($original)) {
        	case "Mage_Sales_Model_Order_Address":
        		if ($cid = $original->getCustomerAddressId()) {
        			$this->id = 'ca_'.$cid;
        		} else {
        			$this->id = 'oa_'.$id;
        		}
        		break;
        	case "Mage_Sales_Model_Quote_Address":
        		if ($cid = $original->getCustomerAddressId()) {
        			$this->id = 'ca_'.$cid;
        		} else {
        			$this->id = 'qa_'.$id;
        		}
        		break;
        	case "Mage_Customer_Model_Address": 
        		$this->id = 'ca_'.$id;
        		break;
        	default: 
        		$this->id = $id;
       	}

		if ($email = $original->getEmail()) {
			$this->email = $email;
		} elseif (is_object($order = $original->getOrder()) && $customerEmail = $order->getCustomerEmail()) {
			$this->email = $customerEmail;
		} elseif (is_object($quote = $original->getQuote()) && $customerEmail = $quote->getCustomerEmail()) { 
			$this->email = $customerEmail;
       	}
        
        return $this;
    }

    /**
     *  Return the type of this address
     *  @return     array {0-2} (billing|shipping)
     */
    public function type()
    {
		return $this->type;
    }
    
    /**
     *  The customer may return null
     *  @return     Copernica_MarketingSoftware_Model_Abstraction_Customer
     */
    public function customer()
    {
		if ($this->customerId) {
            return Mage::getModel('marketingsoftware/abstraction_customer')->loadCustomer($this->customerId);
        } else {
        	return null;
        }
    }
    
    /**
     *  We want to return a unique id, but to do this we need to append
     *  a prefix based on the type of address
     *  @return string
     */
    public function id()
    {
		return $this->id;
    }
    
    /**
     *  Return the name belonging to this address
     *  @return     Copernica_MarketingSoftware_Model_Abstraction_Name
     */
    public function name()
    {
		return $this->name;
    }

    /**
     *  Return the e-mail address of this address
     *  @return     string
     */
    public function email()
    {
		return $this->email;
    }

    /**
     *  Return the street of this addresses
     *  @return     string
     */
    public function street()
    {
		return $this->street;
    }

    /**
     *  Get the city of this addresses
     *  @return     string
     */
    public function city()
    {
		return $this->city;
    }

    /**
     *  Get the zipcode of this addresses
     *  @return     string
     */
    public function zipcode()
    {
		return $this->zipcode;
    }

    /**
     *  Get the state of this addresses
     *  @return     string
     */
    public function state()
    {
 		return $this->state;
    }

    /**
     *  Get the countrycode of this addresses
     *  @return     string
     */
    public function countryCode()
    {
		return $this->countryCode;
    }

    /**
     *  Get the telephone number of this addresses
     *  @return     string
     */
    public function telephone()
    {
		return $this->telephone;
    }

    /**
     *  Get the fax number of this addresses
     *  @return     string
     */
    public function fax()
    {
		return $this->fax;
    }

    /**
     *  Get the company of this address
     *  @return     string
     */
    public function company()
    {
		return $this->company;
    }

    /**
     *  Serialize the object
     *  @return     string
     */
    public function serialize()
    {
        // serialize the data
        return serialize(array(
            $this->id(),
            $this->type(),
            $this->name(),
            $this->email(),
            $this->street(),
            $this->city(),
            $this->zipcode(),
            $this->state(),
            $this->countryCode(),
            $this->telephone(),
            $this->fax(),
            $this->company(),
            is_object($customer = $this->customer()) ? $customer->id() : null,
        ));
    }

    /**
     *  Unserialize the object
     *  @param      string
     *  @return     Copernica_MarketingSoftware_Model_Abstraction_Address
     */
    public function unserialize($string)
    {
        list(
            $this->id,
            $this->type,
            $this->name,
            $this->email,
            $this->street,
            $this->city,
            $this->zipcode,
            $this->state,
            $this->countryCode,
            $this->telephone,
            $this->fax,
            $this->company,
            $this->customerId
        ) = unserialize($string);
        return $this;
    }
}