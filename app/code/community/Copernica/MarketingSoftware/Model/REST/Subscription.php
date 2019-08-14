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
 *  Entity that will take care of synchronizing subscription with copernica.
 */
class Copernica_MarketingSoftware_Model_REST_Subscription extends Copernica_MarketingSoftware_Model_REST
{
    /**
     *  Subscription entity that we will use to create proper profile inside
     *  copernica database.
     *  @var Copernica_MarketingSoftware_Model_Copernica_Entity_Subscription
     */
    private $subscription;

    /**
     *  Construct REST subscription model
     *  @param Copernica_MarketingSoftware_Model_Copernica_Entity_Subscription
     */
    public function __construct($subscription)
    {
        $this->subscription = $subscription;
    }

    /**
     *  Get data that should be update in copernica database
     *  @return array
     */
    private function getProfileData($storeView)
    {
        // get profile fields
        $linkedCustomerFields = Mage::helper('marketingsoftware/config')->getLinkedCustomerFields();

        return array(
            'customer_id' => $this->subscription->getCustomerId(),
            $linkedCustomerFields['email'] => $this->subscription->getEmail(),
            $linkedCustomerFields['group'] => $this->subscription->getGroup(),
            $linkedCustomerFields['newsletter'] => $this->subscription->getStatus(),
            $linkedCustomerFields['storeView'] => $storeView
        );
    }

    /**
     *  Synchronize magento subscriber with copernica profile.
     *  @param  int
     *  @return boolean
     */
    public function sync()
    {
        // get store view Id
        $storeViewId = $this->subscription->getStoreId();

        // get objects related to store
        $store = Mage::getModel('core/store')->load($storeViewId);
        $website = $store->getWebsite();
        $group = $store->getGroup();

        // construct store view identifier
        $storeView = implode(' > ', array (
            $website->getName(), 
            $group->getName(), 
            $store->getName())
        );

        // get profileId
        $profileId = Mage::helper('marketingsoftware/api')->getProfileId(array(
            'id' => null,
            'email' => $this->subscription->getEmail(),
            'storeView' => $storeView
        ));

        // bring request into local scope
        $request = Mage::helper('marketingsoftware/RESTRequest');

        // check if we have profile Id
        if ($profileId) {
            $request->put('/profile/'.$profileId.'/fields/', $this->getProfileData($storeView));
        }

        // we can update old profile
        else {
            // get database Id
            $databaseId = Mage::helper('marketingsoftware/config')->getDatabaseId();

            // create new profile
            $request->post('/database/'.$databaseId.'/profiles/', $this->getProfileData($storeView));
        }

        // we should be fine
        return true;
    }
}
