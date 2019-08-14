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
     *  @var Mage_Customer_Model_Address
     */
    protected $address = null;

    /**
     *  Construct address entity
     *
     *  Constructor is expecting to receive one of magento address types. It would
     *  be so more helpful if all addresses would be unified by an interface 
     *  or common class that we can use, but that seems to be wishful thinking.
     *  
     *  @param Mage_Customer_Model_Address|Mage_Sales_Model_Order_Address|Mage_Sales_Model_Quote_Address
     */
    public function __construct($address)
    {
        $this->address = $address;
    }

    /**
     *  Due to legacy code this method is complicated. 
     *  @return string
     */
    public function fetchId()
    {
        switch (get_class($this->address))
        {
            case 'Mage_Customer_Model_Address': return 'ca_'.$this->address->getId();
            case 'Mage_Sales_Model_Order_Address':
                if ($customerAddressId = $this->address->getCustomerAddressId()) return 'ca_'.$customerAddressId;
                else return 'oa_'.$this->address->getId();
            case 'Mage_Sales_Model_Quote_Address':
                if ($customerAddressId = $this->address->getCustomerAddressId()) return 'ca_'.$customerAddressId;
                else return 'ca_'.$customerAddressId;
            default: return $this->address->getId();
        }
    }

    /**
     *  Get address email
     *  @return string
     */
    public function fetchEmail()
    {
        // try to get email from address model
        if ($email = $this->address->getEmail()) return $email;

        // well, we can try to get email from order model
        if (is_object($order = $this->address->getOrder()) && $email = $order->getCustomerEmail()) return $email;

        // well, another try... Quote may have an email address that we can use
        if (is_object($quote = $this->address->getQuote()) && $email = $quote->getCustomerEmail()) return $email;

        // maybe customer will have an email ?
        if (is_object($customer = $this->address->getCustomer()) && $email = $customer->getEmail()) return $email;

        // nope, we have no clue about email
        return '';
    }

    /**
     *  Fetch name object for this address
     *  @return Copernica_MarketingSoftware_Model_Abstraction_Name
     */
    public function fetchName()
    {
        return Mage::getModel('marketingsoftware/abstraction_name')->setOriginal($this->address);
    }    

    /**
     *  Fetch firstname
     *  @return string
     */
    public function fetchFirstname()
    {
        return $this->getName()->firstname();
    }

    /**
     *  Fetch middlename
     *  @return string
     */
    public function fetchMiddlename()
    {
        return $this->getName()->middlename();
    }

    /**
     *  Fetch lastname
     *  @return string
     */
    public function fetchLastname()
    {
        return $this->getName()->lastname();
    }

    /**
     *  Fetch prefix
     *  @return string
     */
    public function fetchPrefix()
    {
        return $this->getName()->prefix();
    }

    /**
     *  Fetch street
     *  @return string
     */
    public function fetchStreet()
    {
        return $this->address->getStreetFull();
    }

    /**
     *  Fetch city
     *  @return string
     */
    public function fetchCity()
    {
        return $this->address->getCity();
    }

    /**
     *  Fetch postal code 
     *  @return string
     */
    public function fetchZipcode()
    {
        return $this->address->getPostcode();
    }

    /**
     *  Fetch state
     *  @return string
     */
    public function fetchState()
    {
        return $this->address->getRegion();
    }

    /**
     *  Fetch country
     *  @return string
     */
    public function fetchCountryId()
    {
        return $this->address->getCountryCode();
    }

    /**
     *  Fetch company
     *  @return string
     */
    public function fetchCompany()
    {
        return $this->address->getCompany();
    }

    /**
     *  Fetch telephone
     *  @return string
     */
    public function fetchTelephone()
    {
        return $this->address->getTelephone();
    }

    /**
     *  Fetch fax
     *  @return string
     */
    public function fetchFax()
    {
        return $this->address->getFax();
    }

    /**
     *  Get rest entity for given address
     *  @return Copernica_MarketingSoftware_Model_REST_Address
     */
    public function getREST()
    {
        return new Copernica_MarketingSoftware_Model_REST_Address($this);
    }
}