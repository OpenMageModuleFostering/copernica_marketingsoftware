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
 *  REST bridge between magento customer and copernica platform
 */
class Copernica_MarketingSoftware_Model_REST_Customer extends Copernica_MarketingSoftware_Model_REST
{
    /**
     *  Customer that will be used to send data
     *  @var Copernica_MarketingSoftware_Model_Copernica_Entity_Customer
     */
    private $customer;

    /**
     *  Construct REST entity
     *  @param Copernica_MarketingSoftware_Model_Copernica_Entity_Customer
     */
    public function __construct(Copernica_MarketingSoftware_Model_Copernica_Entity_Customer $customer)
    {
        $this->customer = $customer;
    }

    /**
     *  Set customer
     *  @return bool
     */
    public function set()
    {
        // get profile Id
        $profileId = $this->customer->getProfileId();

        // do we have to create a profile
        if ($profileId)
        {
            // make a PUT request to create a profile
            Mage::helper('marketingsoftware/RESTRequest')->put('/profile/'.$profileId.'/fields', $this->getProfileData());
        }
        // we have to update the profile
        else
        {
            // get database Id
            $databaseId = Mage::helper('marketingsoftware/config')->getDatabaseId();
            
            // make a POST request to create a profile
            Mage::helper('marketingsoftware/RESTRequest')->post('/database/'.$databaseId.'/profiles', $this->getProfileData());
        }

        // we are done here
        return true;
    }

    /**
     *  Get data that will be used to create a profile inside copernica platform
     *  @return array
     */
    private function getProfileData()
    {
        // get synced fields
        $syncedFields = Mage::helper('marketingsoftware/config')->getLinkedCustomerFields();

        // get data to sync
        $data = $this->getRequestData($this->customer, $syncedFields);

        // we will also have to set the customer Id
        $data = array_merge($data, array('customer_id' => $this->customer->getCustomerId()));

        // return complete data
        return $data;
    }
}
