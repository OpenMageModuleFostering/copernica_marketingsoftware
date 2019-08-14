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
class Copernica_MarketingSoftware_Model_Copernica_Profile_Subscription extends Copernica_MarketingSoftware_Model_Copernica_Profile
{
    /**
     *  @var    Copernica_MarketingSoftware_Model_Abstraction_Subscription
     */
    protected $_abstractSubscription = false;

    /**
     * Set the subscription object to this object.
     * 
     * @param    Copernica_MarketingSoftware_Model_Abstraction_Subscription    $subscription
     * @return Copernica_MarketingSoftware_Model_Copernica_Profile_Subscription
     */
    public function setSubscription(Copernica_MarketingSoftware_Model_Abstraction_Subscription $subscription)
    {
        $this->_abstractSubscription = $subscription;
        
        return $this;
    }

    /** 
     *  Get profile email.
     *  
     *  @return string
     */
    public function email()
    {
        return (string)$this->_abstractSubscription->storeView();
    }

    /**
     *  Return store view associated with profile
     *  
     *  @return string
     */
    public function storeView()
    {
        return (string)$this->_abstractSubscription->storeview();
    }
    
    /** 
     * Retrieve the data for this object.
     * 
     * @return array
     */
    protected function _data()
    {
        $customerId = null;

        if ($customer = $this->_abstractSubscription->customer()) {
            $customerId = Mage::helper('marketingsoftware/profile')->getCustomerCopernicaId($customer, $this->_abstractSubscription->storeView());
        } else {
            $customerId = Mage::helper('marketingsoftware/profile')->getEmailCopernicaId($this->_abstractSubscription->email(), $this->_abstractSubscription->storeView());
        }

        return array(
            'customer_id'   =>  $customerId,
            'store_view'    =>  (string)$this->_abstractSubscription->storeView(),
            'email'         =>  $this->_abstractSubscription->email(),
            'group'         =>  Mage::getModel('customer/group')->load(0)->getCode(),
            'newsletter'    =>  $this->_abstractSubscription->status(),
        );
    }
}
