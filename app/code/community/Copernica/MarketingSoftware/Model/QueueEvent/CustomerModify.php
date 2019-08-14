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
 *  A wrapper object around an event
 */
class Copernica_MarketingSoftware_Model_QueueEvent_CustomerModify extends Copernica_MarketingSoftware_Model_QueueEvent_Abstract
{
    /**
     *  Customer instance
     */
    private $customer;

    /**
     *  Profile Id
     *  @var int
     */
    private $profileId;

     /**
     *  Process this item in the queue
     *  @return boolean was the processing successfull
     */
    public function process()
    {
        /*
         *  We will need Api instance, customer instance, customer data and 
         *  target profile Id.
         */
        $api = Mage::helper('marketingsoftware/api');
        $this->customer = $this->getObject();
        $customerData = $this->getCustomerData();

        // update profiles, this will create a profile if it does not exists
        $api->updateProfiles($customerData);

        // get profile Id
        $profileId = $api->getProfileId($customerData);

        /*
         *  It's possible that we will be trying to update a customer that is not
         *  yet present in copernica database. In such situation we should create
         *  it's profile so we can use profileId for subprofiles. Thus there is
         *  no point in waiting till profile is created. Instead we will send 
         *  request to create profile and respawn this event. This way we will not
         *  be waitning and therefore we will not block other events.
         */
        if ($profileId === false)
        {
            // respawn this event with the same data
            $this->respawn();

            // we are done here
            return true;
        }

        // cache profile Id
        $this->profileId = $profileId;

        // update customer addresses
        $this->updateCustomerAddresses();

        // this customer is processed
        return true;
    }

    /**
     *  Get customer data
     *  @return Copernica_MarketingSoftware_Model_Copernica_ProfileCustomer
     */
    private function getCustomerData()
    {
        return Mage::getModel('marketingsoftware/copernica_profilecustomer')
            ->setCustomer($this->customer)
            ->setDirection('copernica');
    }

    /**
     *  Update all customer addresses
     */
    private function updateCustomerAddresses()
    {
        // get Api instance
        $api = Mage::helper('marketingsoftware/api');

        // iterate over all addresses
        foreach($this->customer->addresses() as $address)
        {
            $api->updateAddressSubProfiles($this->profileId, $this->getAddressData($address));
        }
    }

    /**
     *  Get address data
     *  @return Copernica_MarketingSoftware_Model_Copernica_Address_Subprofile
     */
    private function getAddressData($address)
    {
        return Mage::getModel('marketingsoftware/copernica_address_subprofile')
            ->setAddress($address)
            ->setDirection('copernica');
    }
}