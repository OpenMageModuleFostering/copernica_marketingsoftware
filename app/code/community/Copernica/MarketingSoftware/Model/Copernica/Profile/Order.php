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
 *  An object to wrap the Copernica profile
 */
class Copernica_MarketingSoftware_Model_Copernica_Profile_Order extends Copernica_MarketingSoftware_Model_Copernica_Profile
{
    /**
     *  @var    Copernica_MarketingSoftware_Model_Abstraction_Order
     */
    protected $_order = false;

    /**
     *  Set the order object to this object  
     *  
     *  @param    Copernica_MarketingSoftware_Model_Abstraction_Order    $order
     *  @return Copernica_MarketingSoftware_Model_Copernica_Profile_Order
     */
    public function setOrder(Copernica_MarketingSoftware_Model_Abstraction_Order $order)
    {
        $this->_order = $order;
        return $this;
    }

    /** 
     *  Get profile email.
     *  
     *  @return string
     */
    public function email()
    {
        $addresses = $this->_order->addresses();
    
        foreach ($addresses as $address) {
            if (in_array('billing', $addr->type())) {
                return $address->email();  
            }
        } 

        return '';
    }

    /**
     *  Return store view associated with profile
     *  
     *  @return string
     */
    public function storeView()
    {
        return (string)$this->_order->storeview();
    }
    
    /** 
     *  Retrieve the data for this object
     *  
     *  @return array
     */
    protected function _data()
    {    
        $addresses = $this->_order->addresses();
        
        if (count($addresses) == 1) {
            $address = $addresses[0];
        } else {
            foreach ($addresses as $addr) {
                if (in_array('billing', $addr->type())) {
                    $address = $addr;
                }
            }
        }

        if (Mage::getModel('newsletter/subscriber')->loadByEmail($address->email())->getId()) {
            $subscription = Mage::getModel('marketingsoftware/abstraction_subscription')
                ->setOriginal(Mage::getModel('newsletter/subscriber')->loadByEmail($address->email()));
        } else {
            $subscription = false;
        }

        $name = $address->name();

        $customerId = null;

        if ($customer = $this->_order->customer()) {
            $customerId = Mage::helper('marketingsoftware/profile')->getCustomerCopernicaId($customer, $this->_order->storeview());
        } else {
            $customerId = Mage::helper('marketingsoftware/profile')->getEmailCopernicaId($address->email(), (string)$this->_order->storeview());
        }

        return array(
            'customer_id'   =>  $customerId,
            'store_view'    =>  (string)$this->_order->storeview(),
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
