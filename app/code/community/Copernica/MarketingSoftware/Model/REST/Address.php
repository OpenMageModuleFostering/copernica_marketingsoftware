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
 *  Address REST entity
 */
class Copernica_MarketingSoftware_Model_REST_Address extends Copernica_MarketingSoftware_Model_REST
{
    /**
     *  Cached address entity
     *  @var Coeprnica_MarketingSoftware_Model_Copernica_Entity_Address
     */
    private $address;

    /**
     *  Construct REST entity
     *  @param Copernica_MarketingSoftware_Model_Copernica_Entity_Address
     */
    public function __construct($address)
    {
        $this->address = $address;
    }

    /**
     *  Bind address to customer
     *  @param  Copernica_MarketingSoftware_Model_Copernica_Entity_Customer
     *  @return boolean
     */
    public function bindToCustomer(Copernica_MarketingSoftware_Model_Copernica_Entity_Customer $customer)
    {
        // get profile Id
        $profileId = Mage::helper('marketingsoftware/api')->getProfileId(array(
            'id' => $customer->getId(),
            'email' => $customer->getEmail(),
            'storeView' => $customer->getStoreView(),
        ));

        // sync data with profile
        $this->syncWithProfile($profileId);

        // everything went just ok
        return true;
    }

    /**
     *  Sync address data with certain profile
     *  @param  int
     *  @return bool    Did we succeed?
     */
    public function syncWithProfile($profileId)
    {
        // get address collection Id
        $addressCollectionId = Mage::helper('marketingsoftware/config')->getAddressesCollectionId();

        // make a PUT request
        if ($addressCollectionId)
        {
            Mage::helper('marketingsoftware/RESTRequest')->put('/profile/'.$profileId.'/subprofiles/'.$addressCollectionId, $this->getSubprofileData(), array(
                'fields[]' => 'address_id=='.$this->address->getId(),
                'create' => true
            ));  

            return true;
        } 

        // we didn't make the sync
        else return false;
    }

    /**
     *  Get subprofile data
     *  @return array
     */
    private function getSubprofileData()
    {
        // get addresses synced fields
        $syncedFields = Mage::helper('marketingsoftware/config')->getLinkedAddressFields();

        // get data
        $data = $this->getRequestData($this->address, $syncedFields);

        // merge subprofile data and required fields
        $data = array_merge($data, array('address_id' => $this->address->getId()));

        // return subprofile data
        return $data;
    }
}