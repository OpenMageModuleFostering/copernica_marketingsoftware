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
 *  This class is a bridge class between magento customer and copernica profile
 */
class Copernica_MarketingSoftware_Model_Copernica_Entity_Customer extends Copernica_MarketingSoftware_Model_Copernica_Entity
{
    /**
     *  Magento customer
     *  @var Mage_Customer_Model_Customer
     */
    private $customer = null;

    /**
     *  Array of customer addresses
     *  @var array
     */
    private $addresses = null;

    /**
     *  Array of customer orders
     *  @var array
     */
    private $orders = null;

    /**
     *  Cache profile Id
     *  @var string
     */
    private $profileId = null;

    /**
     *  Construct customer entity
     *  @param int
     */
    public function __construct($customerId)
    {
        // get magento customer
        $customer = Mage::getModel('customer/customer')->load($customerId);

        // check if it's a new customer
        if (!$customer->isObjectNew()) $this->customer = $customer;

        // it's an existing customer
        else throw Mage::exception('Copernica_MarketingSoftware', 'Customer does not exists', Copernica_MarketingSoftware_Exception::CUSTOMER_NOT_EXISTS);
    }

    /**
     *  Get customer store view
     *  @return Copernica_MarketingSoftware_Model_Abstraction_StoreView
     */
    public function fetchStoreView()
    {
        return Mage::getModel('marketingsoftware/abstraction_storeview')->setOriginal($this->customer->getStore());
    }

    /**
     *  Get customer id. Magento customer Id
     *  @return string
     */
    public function fetchId()
    {
        return $this->customer->getId();
    }

    /**
     *  Our unique customer ID in form of customer_ID|storeView_ID
     *  @return string
     */
    public function fetchCustomerId()
    {
        return $this->getId().'|'.$this->getStoreView()->id();
    }

    /**
     *  Get customer name
     *  @return Copernica_MarketingSoftware_Model_Abstraction_Name
     */
    public function fetchName()
    {
        return Mage::getModel('marketingsoftware/abstraction_name')->setOriginal($this->customer);
    }

    /**
     *  Get customer firstname
     *  @return string
     */
    public function fetchFirstname()
    {
        return $this->getName()->firstname();
    }

    /**
     *  Get customer middlename
     *  @return string
     */
    public function fetchMiddlename()
    {
        return $this->getName()->middlename();
    }

    /**
     *  Fetch customer lastname
     *  @return string
     */
    public function fetchLastname()
    {
        return $this->getName()->lastname();
    }

    /**
     *  Get customer email
     *  @return string
     */
    public function fetchEmail()
    {
        return $this->customer->getEmail();
    }

    /**
     *  Get registration date of a customer
     *  @return string
     */
    public function fetchRegistrationDate()
    {
        return date('Y-m-d H:i:s', $this->customer->getCreatedAtTimestamp());
    }

    /**
     *  Fetch newsletter status
     *  @return string
     */
    public function fetchNewsletter()
    {
        // get magento subscriber model
        $subscriber = Mage::getModel('newsletter/subscriber')->loadByCustomer($this->customer);

        // if thre is no subscriber we just will return unknown status
        if (!$subscriber->getId()) return 'unknown';

        // return diffetent result depending on subscriber status
        switch($subscriber->getStatus())
        {
            case Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED:
                return 'subscribed';
            case Mage_Newsletter_Model_Subscriber::STATUS_NOT_ACTIVE:
                return 'not active';
            case Mage_Newsletter_Model_Subscriber::STATUS_UNSUBSCRIBED:
                return 'unsubscribed';
            case Mage_Newsletter_Model_Subscriber::STATUS_UNCONFIRMED:
                return 'unconfirmed';
            default:
                return 'unknown';
        }
    }

    /**
     *  Get customer gender
     *  @return string
     */
    public function fetchGender()
    {
        // get gender options
        $options = $this->customer->getAttribute('gender')->getSource()->getAllOptions();

        // get customer idx
        $customerGenderIdx = $this->customer->getGender();

        // iterater over all options to get proper gender
        foreach ($options as $option)
        {
            if ($option['value'] == $customerGenderIdx) return $option['label'];
        }

        // we don't know
        return 'unknown';
    }

    /**
     *  Get customer date of birth
     *  @return string
     */
    public function getBirthDate()
    {
        return $this->customer->getDob();
    }

    /**
     *  Return all customer addresses
     *  @return array
     */
    public function getAddresses()
    {
        if (!is_null($this->addresses)) return $this->addresses;

        // data holder for customer addresses
        $addresses = array();

        // create copernica entities from addresses
        foreach ($this->customer->getAddresses() as $address)
        {
            $addresses[] = new Copernica_MarketingSoftware_Model_Copernica_Entity_Address($address);
        }

        // cache and return all customer addresses
        return $this->addresses = $addresses;
    }

    /**
     *  Get all customer orders
     *  @return array
     */
    public function getOrders()
    {
        // check if we did already cached orders
        if (!is_null($this->orders)) return $this->orders;

        // data holder for customer orders
        $orders = array();

        // get collection with all customers orders
        $ordersCollection = Mage::getModel('sales/order')->getCollection()->addFieldToFilter('customer_id', $this->customer->getId());

        // iterate over all orders
        foreach ($ordersCollection as $order)
        {
            $orders[] = new Copernica_MarketingSoftware_Model_Copernica_Entity_Order($order);
        }

        // return all customer orders and cache them
        return $orders;
    }

    /**
     *  Get customer group
     *  @return string
     */
    public function fetchGroup()
    {
        // fetch customer group
        return Mage::getModel('customer/group')->load($this->customer->getGroupId())->getCode();
    }

    /**
     *  Get profile
     *  @return Copernica_MarketingSoftware_Model_REST_Customer
     */
    public function getREST()
    {
        return new Copernica_MarketingSoftware_Model_REST_Customer($this);
    }

    /**
     *  Get profile Id
     *  @return string|false
     */
    public function getProfileId()
    {
        // check if we already stored profile Id
        if (!is_null($this->profileId)) return $this->profileId;

        // try to fetch profile Id from API helper
        $profileId = Mage::helper('marketingsoftware/api')->getProfileId(array(
            'id' => $this->getCustomerId(),
            'email' => $this->getEmail(),
            'storeView' => strval($this->getStoreView())
        ));

        // if we have a profile Id we can cache it and return it
        if ($profileId) return $this->profileId = $profileId;

        // we don't have any sensible info, so just return false
        return false;
    }
}
