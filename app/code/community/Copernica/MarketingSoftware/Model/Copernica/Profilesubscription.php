<?php
/**
 *  An object to wrap the Copernica profile
 */
class Copernica_MarketingSoftware_Model_Copernica_ProfileSubscription extends Copernica_MarketingSoftware_Model_Copernica_Profile
{
    /**
     *  @var Copernica_MarketingSoftware_Model_Abstraction_Customer
     */
    private $subscription = false;

    /**
     *  Set the customer object to this object
     *  @param Copernica_MarketingSoftware_Model_Abstraction_Subscription
     *  @return Copernica_MarketingSoftware_Model_Copernica_ProfileCustomer
     */
    public function setSubscription($customer)
    {
        $this->subscription = $customer;
        return $this;
    }
    
    /** 
     *  Retrieve the data for this object
     *  @return array
     */
    protected function _data()
    {
        // return an array with customer data
        return array(
            'customer_id'   =>  Mage::helper('marketingsoftware')->generateCustomerId($this->subscription->email(), (string)$this->subscription->storeView()),
            'store_view'    =>  (string)$this->subscription->storeView(),
            'email'         =>  $this->subscription->email(),
            'group'         =>  Mage::getModel('customer/group')->load(0)->getCode(),
            'newsletter'    =>  $this->subscription->status(),
        );
    }
}