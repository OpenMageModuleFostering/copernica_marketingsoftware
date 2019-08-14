<?php
/**
 *  An object to wrap the Copernica profile
 */
class Copernica_MarketingSoftware_Model_Copernica_ProfileCustomer extends Copernica_MarketingSoftware_Model_Copernica_Profile
{
    /**
     *  @var Copernica_MarketingSoftware_Model_Abstraction_Customer
     */
    private $customer = false;

    /**
     *  Set the customer object to this object
     *  @param Copernica_MarketingSoftware_Model_Abstraction_Customer
     *  @return Copernica_MarketingSoftware_Model_Copernica_ProfileCustomer
     */
    public function setCustomer($customer)
    {
        $this->customer = $customer;
        return $this;
    }

    /**
     *  Retrieve the data for this object
     *  @return array
     */
    protected function _data()
    {
        // Get the subscription
        $subscription = $this->customer->subscription();

        // fetch the name object
        $name = $this->customer->name();

        $email = is_object($subscription) ? $subscription->email() : $this->customer->email();

        // return an array with customer data
        return array(
            'customer_id'   =>  Mage::helper('marketingsoftware')->generateCustomerId($email, (string)$this->customer->storeview()),
            'store_view'    =>  (string)$this->customer->storeview(),
            'firstname'     =>  is_object($name) ? $name->firstname() : null,
            'middlename'    =>  is_object($name) ? $name->middlename() : null,
            'lastname'      =>  is_object($name) ? $name->lastname() : null,
            'email'         =>  $email,
            'group'         =>  $this->customer->group(),
            'newsletter'    =>  is_object($subscription) ? $subscription->status() : 'unknown',
            'gender'        =>  $this->customer->gender() ? $this->customer->gender() : 'unknown'
        );
    }
}