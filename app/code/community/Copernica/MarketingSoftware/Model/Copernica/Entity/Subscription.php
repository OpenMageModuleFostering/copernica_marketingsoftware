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
 *  Copernica subscription model
 */
class Copernica_MarketingSoftware_Model_Copernica_Entity_Subscription extends Copernica_MarketingSoftware_Model_Copernica_Entity
{
    /**
     *  Our subscriber
     *  
     *  @var	Mage_Newsletter_Model_Subscriber
     */
    protected $_subscriber;

    /**
     *  Fetch email address
     *  
     *  @return string
     */
    public function fetchEmail()
    {
        return $this->_subscriber->getEmail();
    }

    /**
     *  Fetch status
     *  
     *  @return string
     */
    public function fetchStatus()
    {
        switch ($this->_subscriber->getStatus()) {
            case Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED:
                return 'subscribed';
                
            case Mage_Newsletter_Model_Subscriber::STATUS_NOT_ACTIVE:
                return 'not active';
                
            case Mage_Newsletter_Model_Subscriber::STATUS_UNSUBSCRIBED:
                return 'unsubscribed';
                
            case Mage_Newsletter_Model_Subscriber::STATUS_UNCONFIRMED:
                return 'unconfirmed';
                
            default:
                return 'unknown';
        }       
    }

    /**
     *  Fetch group
     *  
     *  @return string
     */
    public function fetchGroup()
    {
        return Mage::getModel('customer/group')->load(0)->getCode();
    }

    /**
     *  Fetch store view
     *  
     *  @return string
     */
    public function fetchStoreView()
    {
    	$store = Mage::getModel('core/store')->load($this->getStoreId());
    	
    	return Mage::getModel('marketingsoftware/abstraction_storeview')->setOriginal($store);   
    }

    /**
     *  Fetch subscriber customer Id
     *  
     *  @return string
     */
    public function fetchCustomerId()
    {
        $store = Mage::getModel('core/store')->load($this->_subscriber->getStoreId());
        
        $customer = Mage::getModel('customer/customer')->setWebsiteId($store->getWebsiteId())->loadByEmail($this->_subscriber->getEmail());

        if ($customer->isObjectNew()) {
        	$identifier = $this->_subscriber->getEmail();
        } else {
        	$identifier = $customer->getId();
        }

        return $identifier.'|'.$this->_subscriber->getStoreId();
    }

    /**
     *  Get subscribtion store Id
     *  
     *  @return int
     */
    public function getStoreId()
    {
    	if($this->_subscriber->getStoreId()) {
    		return $this->_subscriber->getStoreId();
    	} else {
    		return 0;
    	}        
    }

    /**
     *  Get REST subscription entity
     *  
     *  @return Copernica_MarketingSoftware_Model_Rest_Subscription
     */
    public function getRestSubscription()
    {
    	$restSubscription = Mage::getModel('marketingsoftware/rest_subscription');
    	$restSubscription->setSubscriptionEntity($this);
    	
    	return $restSubscription;
    }
    
    /**
     *  Set subscription entity
     *
     *  @param	Mage_Newsletter_Model_Subscriber	$subscriber
     */
    public function setSubscription($subscriber)
    {
    	$this->_subscriber = $subscriber;
    }
}