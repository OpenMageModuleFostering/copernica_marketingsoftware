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
class Copernica_MarketingSoftware_Model_Copernica_Address_Subprofile extends Copernica_MarketingSoftware_Model_Copernica_Abstract
{
    /**
     *  Return the identifier for this profile
     *  
     *  @return string
     */
    public function id()
    {
        return $this['address_id'];
    }

    /**
     *  Try to store a quote item
     *  
     *  @param    Copernica_MarketingSoftware_Model_Abstraction_Address    $address
     */
    public function setAddress($address)
    {
        $this->address = $address;
        
        return $this;
    }

    /**
     *  Get linked fields
     *  
     *  @return array
     */
    public function linkedFields()
    {
        return Mage::helper('marketingsoftware/config')->getLinkedAddressFields();
    }

    /**
     *  Get the required fields
     *  
     *  @return array
     */
    public function requiredFields()
    {
        return array('address_id');
    }

    /**
     *  Retrieve the data for this object
     *  
     *  @return array
     */
    protected function _data()
    {
        if (($email = $this->address->email()) == "" && is_object($customer = $this->address->customer())) {
            $email = $customer->email();
        }

        $name = $this->address->name();

        return array(
            'address_id'    =>  $this->address->id(),
            'firstname'     =>  is_object($name) ? $name->firstname() : null,
            'prefix'        =>  is_object($name) ? $name->prefix() : null,
            'middlename'    =>  is_object($name) ? $name->middlename() : null,
            'lastname'      =>  is_object($name) ? $name->lastname() : null,
            'email'         =>  $email,
            'company'       =>  $this->address->company(),
            'street'        =>  $this->address->street(),
            'city'          =>  $this->address->city(),
            'state'         =>  $this->address->state(),
            'zipcode'       =>  $this->address->zipcode(),
            'country_id'    =>  $this->address->countryId(),
            'telephone'     =>  $this->address->telephone(),
            'fax'           =>  $this->address->fax(),
        );
    }
}
