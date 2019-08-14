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
class Copernica_MarketingSoftware_Model_Rest_Subscription extends Copernica_MarketingSoftware_Model_Rest
{
    /**
     *  Subscription entity that we will use to create proper profile inside
     *  copernica database.
     *  
     *  @var	Copernica_MarketingSoftware_Model_Copernica_Entity_Subscription
     */
    protected $_subscriptionEntity;

    /**
     *  Get data that should be update in copernica database
     *  
     *  @param	string	$storeView
     *  @return array
     */
    protected function _getProfileData($storeview)
    {
        $linkedCustomerFields = Mage::helper('marketingsoftware/config')->getLinkedCustomerFields();

        return array(
            'customer_id' => $this->_subscriptionEntity->getCustomerId(),
            $linkedCustomerFields['email'] => $this->_subscriptionEntity->getEmail(),
            $linkedCustomerFields['group'] => $this->_subscriptionEntity->getGroup(),
            $linkedCustomerFields['newsletter'] => $this->_subscriptionEntity->getStatus(),
            $linkedCustomerFields['storeView'] => $storeview
        );
    }

    /**
     *  Synchronize magento subscriber with copernica profile.
     *  
     *  @return boolean
     */
    public function sync()
    {
        $storeview = (string) $this->_subscriptionEntity->getStoreView();
    	
        $profileId = Mage::helper('marketingsoftware/api')->getProfileId(array(
            'id' => null,
        	'storeView' => $storeview,
            'email' => $this->_subscriptionEntity->getEmail(),            
        ));

        $request = Mage::helper('marketingsoftware/rest_request');

        if ($profileId) {
            $request->post('/profile/'.$profileId.'/fields/', $this->_getProfileData($storeview));
        } else {
            $databaseId = Mage::helper('marketingsoftware/config')->getDatabaseId();

            $request->post('/database/'.$databaseId.'/profiles/', $this->_getProfileData($storeview));
        }

        return true;
    }
    
    /**
     *  Set REST subscription entity
     *
     *  @param	Copernica_MarketingSoftware_Model_Copernica_Entity_Subscription	$subscriptionEntity
     */
    public function setSubscriptionEntity(Copernica_MarketingSoftware_Model_Copernica_Entity_Quote $subscriptionEntity) 
    {
    	$this->_subscriptionEntity = $subscriptionEntity;
    }
}
