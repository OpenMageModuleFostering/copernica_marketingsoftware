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
class Copernica_MarketingSoftware_Model_Copernica_ProfileCustomer extends Copernica_MarketingSoftware_Model_Copernica_Profile
{
    /**
     *  @var Copernica_MarketingSoftware_Model_Abstraction_Customer
     */
    protected $customer = false;

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
     * Returns customer_id based on the original (previous) email, if possible.
     * Falls back on the customer_id using the current email.
     * 
     * @return string
     */
    public function originalId()
    {
    	// If the email address has been changed return the old id
    	if ($this->customer->oldEmail() != $this->customer->email()) {
    		$email = $this->customer->oldEmail();
    	} else {
    		$email = $this->customer->email();
    	}
    	
    	return Mage::helper('marketingsoftware')->generateCustomerId($email, (string) $this->customer->storeview());
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

        $email = $this->customer->email();
        
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