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
class Copernica_MarketingSoftware_Model_Copernica_Profile_Customer extends Copernica_MarketingSoftware_Model_Copernica_Profile
{
    /**
     *  @var	Copernica_MarketingSoftware_Model_Abstraction_Customer
     */
    protected $_customer = false;

    /**
     *  Set the customer object to this object
     *  
     *  @param	Copernica_MarketingSoftware_Model_Abstraction_Customer	$customer
     *  @return	Copernica_MarketingSoftware_Model_Copernica_Profile_Customer
     */
    public function setCustomer(Copernica_MarketingSoftware_Model_Abstraction_Customer $customer)
    {
        $this->_customer = $customer;
        
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
        return Mage::helper('marketingsoftware/profile')->getCustomerCopernicaId($this->_customer, $this->_customer->storeview());
    }

    /** 
     *  Get profile email.
     *  
     *  @return string
     */
    public function email()
    {
        return $this->_customer->email();
    }

    /**
     *  Return store view associated with profile.
     *  
     *  @return string
     */
    public function storeView()
    {
        return (string)$this->_customer->storeview();
    }

    /**
     *  Retrieve the data for this object
     *  
     *  @return array
     */
    protected function _data()
    {
        $subscription = $this->_customer->subscription();

        $name = $this->_customer->name();

        $email = $this->_customer->email();

        $storeview = $this->_customer->storeview();

        $customerId = Mage::helper('marketingsoftware/profile')->getCustomerCopernicaId($this->_customer, $storeview);

        $customerBirthDate = $this->_customer->birthDate();

        if(is_object($customerBirthDate))
            $birthDate = $customerBirthDate->gmtDate();
        elseif(is_string($customerBirthDate) && strtotime($customerBirthDate))
            $birthDate = $customerBirthDate;
        else $birthDate = '0000-00-00';

        return array(
            'customer_id'   =>  $customerId,
            'store_view'    =>  (string)$storeview,
            'firstname'     =>  is_object($name) ? $name->firstname() : null,
            'middlename'    =>  is_object($name) ? $name->middlename() : null,
            'lastname'      =>  is_object($name) ? $name->lastname() : null,
            'email'         =>  $email,
            'birthdate'     =>  $birthDate,
            'group'         =>  $this->_customer->group(),
            'newsletter'    =>  is_object($subscription) ? $subscription->status() : 'unknown',
            'gender'        =>  $this->_customer->gender()
        );
    }
}
