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
 *  REST entity that will communicate with Copernica platform
 */
class Copernica_MarketingSoftware_Model_REST_Product extends Copernica_MarketingSoftware_Model_REST
{
    /**
     *  Cached product entity
     *  @param Copernica_MarketingSoftware_Model_Copernica_Entity_Product
     */
    private $product;

    /**
     *  Construct REST entity
     *  @param Copernica_MarketingSoftware_Model_Copernica_Entity_Product
     */
    public function __construct($productEntity)
    {
        $this->product = $productEntity;
    }

    /**
     *  Get subprofile data
     *  @return array
     */
    private function getSubprofileData()
    {
        // get fields that are linked
        $syncedFields = Mage::helper('marketingsoftware/config')->getLinkedViewedProductFields();

        // get request data
        return $this->getRequestData($this->product, $syncedFields);
    }

    /**
     *  Tell Copernica platform that product was viewed by a customer
     *  @param Copernica_MarketingSoftware_Model_Copernica_Entity_Customer
     */
    public function viewedBy(Copernica_MarketingSoftware_Model_Copernica_Entity_Customer $customer)
    {
        // get profile Id
        $profileId = Mage::helper('marketingsoftware/api')->getProfileId(array(
            'id' => $customer->getCustomerId(),
            'storeView' => $customer->getStoreView(),
            'email' => $customer->getEmail(),
        ));

        // check if we have a profile Id
        if ($profileId === false && !($profileId = $this->createProfile($customer))) return false; 

        // get vieved product collection Id
        $collectionId = Mage::helper('marketingsoftware/config')->getViewedProductCollectionId();

        // make a PUT request to update/create subprofile
        if ($collectionId) Mage::helper('marketingsoftware/RESTRequest')->put('/profile/'.$profileId.'/subprofiles/'.$collectionId, $this->getSubprofileData(), array(
            'fields[]' => 'id=='.$this->product->getId(), 
            'create' => 'true'
        ));
    }
}