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
 *  Copernica subscription model
 */
class Copernica_MarketingSoftware_Model_Copernica_Entity_Subscription extends Copernica_MarketingSoftware_Model_Copernica_Entity
{
    /**
     *  Our subscriber
     *  @var Mage_Newsletter_Model_Subscriber
     */
    private $subscription;

    /**
     *  Construct subscription model
     *  @param Mage_Newsletter_Model_Subscriber
     */
    public function __construct($subscription)
    {
        $this->subscription = $subscription;
    }

    /**
     *  Fetch email address
     *  @return string
     */
    public function fetchEmail()
    {
        return $this->subscription->getEmail();
    }

    /**
     *  Fetch status
     *  @return string
     */
    public function fetchStatus()
    {
        switch ($this->subscription->getStatus()) {
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
     *  @return string
     */
    public function fetchGroup()
    {
        return Mage::getModel('customer/group')->load(0)->getCode();
    }

    /**
     *  Fetch store view
     *  @return string
     */
    public function fetchStoreView()
    {
        // get magento store
        $store = Mage::getModel('core/store')->load($this->subscription->getStoreId());

        // if we don't have a store we are just about done here
        if (is_null($store)) return '';  

        // parse store to string
        return implode(' > ', array(
            $store->getWebsite()->getName(), 
            $store->getGroup()->getName(), 
            $store->getName()
        ));
    }

    /**
     *  Fetch subscriber customer Id
     *  @return string
     */
    public function fetchCustomerId()
    {
        // get store model
        $store = Mage::getModel('core/store')->load($this->subscription->getStoreId());

        // get customer that would be associated with given email address
        $customer = Mage::getModel('customer/customer')->setWebsiteId($store->getWebsiteId())->loadByEmail($this->subscription->getEmail());

        // construct proper id
        if ($customer->isObjectNew()) $identifier = $this->subscription->getEmail();
        else $identifier = $customer->getId();

        // return customer Id
        return $identifier.'|'.$this->subscription->getStoreId();
    }

    /**
     *  Get subscribtion store Id
     *  @return int
     */
    public function getStoreId()
    {
        return $this->subscription->getStoreId();
    }

    /**
     *  Construct REST entity that will take care of synchronization
     *  @return Copernica_MarketingSoftware_Model_REST_Subscription
     */
    public function getREST()
    {
        return new Copernica_MarketingSoftware_Model_REST_Subscription($this);
    }
}