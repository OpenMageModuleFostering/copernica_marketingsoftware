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
class Copernica_MarketingSoftware_Model_Copernica_ProfileOrder extends Copernica_MarketingSoftware_Model_Copernica_Profile
{
    /**
     *  @var Copernica_MarketingSoftware_Model_Abstraction_Order
     */
    protected $order = false;

    /**
     *  Set the customer object to this object
     *  @param Copernica_MarketingSoftware_Model_Abstraction_Quote
     *  @return Copernica_MarketingSoftware_Model_Copernica_ProfileCustomer
     */
    public function setOrder($order)
    {
        $this->order = $order;
        return $this;
    }

    /** 
     *  Get profile email.
     *  @return string
     */
    public function email()
    {
        // get all addresses associated with order
        $addresses = $this->order->addresses();
    
        // find billing address
        foreach ($addresses as $address) {
            if (in_array('billing', $addr->type())) return $address->email();  
        } 

        // we don't have an address. Return empty string
        return '';
    }

    /**
     *  Return store view associated with profile
     *  @return string
     */
    public function storeView()
    {
        return (string)$this->order->storeview();
    }
    
    /** 
     *  Retrieve the data for this object
     *  @return array
     */
    protected function _data()
    {    
        // Get the addresses
        $addresses = $this->order->addresses();
        
        // Select an address
        if (count($addresses) == 1) $address = $addresses[0];
        else foreach ($addresses as $addr) if (in_array('billing', $addr->type())) $address = $addr;

        // Get subscription
        if (Mage::getModel('newsletter/subscriber')->loadByEmail($address->email())->getId())
        {
            $subscription = Mage::getModel('marketingsoftware/abstraction_subscription')
                ->setOriginal(Mage::getModel('newsletter/subscriber')->loadByEmail($address->email()));
        }
        else $subscription = false;

        // fetch the name object
        $name = $address->name();

        // placeholder for copeernica Id
        $customerId = null;

        // check if we have a customer to generate custoemr Id
        if ($customer = $this->order->customer()) $customerId = Mage::helper('marketingsoftware/profile')->getCustomerCopernicaId($customer, $this->order->storeview());

        // generate customer Id from email address
        else $customerId = Mage::helper('marketingsoftware/profile')->getEmailCopernicaId($address->email(), (string)$this->order->storeview());

        // return an array with customer data
        return array(
            'customer_id'   =>  $customerId,
            'store_view'    =>  (string)$this->order->storeview(),
            'firstname'     =>  is_object($name) ? $name->firstname() : null, 
            'middlename'    =>  is_object($name) ? $name->middlename() : null,
            'lastname'      =>  is_object($name) ? $name->lastname() : null,
            'email'         =>  $address->email(),
            'group'         =>  Mage::getModel('customer/group')->load(0)->getCode(),
            'newsletter'    =>  is_object($subscription) ? $subscription->status() : 'unknown',
            'gender'        =>  'unknown'
        );
    }
}