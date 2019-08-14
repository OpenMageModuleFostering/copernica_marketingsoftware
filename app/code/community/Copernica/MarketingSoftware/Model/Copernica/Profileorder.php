<?php
/**
 *  An object to wrap the Copernica profile
 */
class Copernica_MarketingSoftware_Model_Copernica_ProfileOrder extends Copernica_MarketingSoftware_Model_Copernica_Profile
{
    /**
     *  @var Copernica_MarketingSoftware_Model_Abstraction_Customer
     */
    private $order = false;

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

        // return an array with customer data
        return array(
            'customer_id'   =>  Mage::helper('marketingsoftware')->generateCustomerId($address->email(), (string)$this->order->storeview()),
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