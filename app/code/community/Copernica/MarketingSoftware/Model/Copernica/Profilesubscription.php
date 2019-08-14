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
class Copernica_MarketingSoftware_Model_Copernica_ProfileSubscription extends Copernica_MarketingSoftware_Model_Copernica_Profile
{
    /**
     *  @var Copernica_MarketingSoftware_Model_Abstraction_Customer
     */
    protected $subscription = false;

    /**
     * Set the customer object to this object.
     * 
     * @param Copernica_MarketingSoftware_Model_Abstraction_Subscription
     * @return Copernica_MarketingSoftware_Model_Copernica_ProfileCustomer
     */
    public function setSubscription($customer)
    {
        $this->subscription = $customer;
        return $this;
    }

    /** 
     *  Get profile email.
     *  @return string
     */
    public function email()
    {
        return (string)$this->subscription->storeView();
    }

    /**
     *  Return store view associated with profile
     *  @return string
     */
    public function storeView()
    {
        return (string)$this->subscription->storeview();
    }
    
    /** 
     * Retrieve the data for this object.
     * 
     * @return array
     */
    protected function _data()
    {
        // placeholder for customer Id
        $customerId = null;

        // check if we have a proper customer
        if ($customer = $this->subscription->customer()) $customerId = Mage::helper('marketingsoftware/profile')->getCustomerCopernicaId($customer, $this->subscription->storeView());
        else $customerId = Mage::helper('marketingsoftware/profile')->getEmailCopernicaId($this->subscription->email(), $this->subscription->storeView());

        // return an array with customer data
        return array(
            'customer_id'   =>  $customerId,
            'store_view'    =>  (string)$this->subscription->storeView(),
            'email'         =>  $this->subscription->email(),
            'group'         =>  Mage::getModel('customer/group')->load(0)->getCode(),
            'newsletter'    =>  $this->subscription->status(),
        );
    }
}