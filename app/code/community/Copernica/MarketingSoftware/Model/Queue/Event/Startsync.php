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
 *  This event when it's being processed should spaw customer/orders/subscriptions
 *  sync events. It should spawn them in batches so event queue will not grow into
 *  cosmic scale.
 */
class Copernica_MarketingSoftware_Model_Queue_Event_Startsync extends Copernica_MarketingSoftware_Model_Queue_Event_Abstract
{
    /** 
     *  How many events we want to spawn for each of customers/orders/subscriptions
     *  
     *  @var	int
     */
    protected $_eventsLimit = 5000;

    /**
     *  Current event status. It contains informations about last entites that were
     *  scheduled for synchronization. Also it does contain stores id that should
     *  be used to filter entites results.
     *  
     *  @var	Copernica_MarketingSoftware_Model_Sync_Status
     */
    protected $_currentStatus;

    /** 
     *  Process event. If not all entities can be added to synchtonization this
     *  event will respawn with new set of data. Note that this event will be 
     *  respawning till all entities will be scheduled to sync.
     *  
     *  @return	boolean 
     */
    public function process() {
        $this->_currentStatus = Copernica_MarketingSoftware_Model_Sync_Status::fromStd($this->_getObject());

        $shouldRespawn = false;

        if ($this->_addCustomersToQueue() == $this->_eventsLimit) {
        	$shouldRespawn = true;
        }

        if ($this->_addOrdersToQueue() == $this->_eventsLimit) {
        	$shouldRespawn = true;
        }

        if ($this->_addSubscriptionsToQueue() == $this->_eventsLimit) {
        	$shouldRespawn = true;
        }

        if ($shouldRespawn) {
        	$this->respawn();
        }

        return true;
    }

    /**
     *  Respawn start sync event.
     */
    public function respawn()
    {
        $queue = Mage::getModel('marketingsoftware/queue_item')
            ->setObject($this->_currentStatus->toArray())
            ->setAction('start_sync')
            ->setName('startsync')
            ->save();

        return true;
    }

    /**
     *  Get ::addAttributeToFilter() compatible array of stores that we want to
     *  sync. Null will be returned when there is no stores to filter.
     *  
     *  @return array|null
     */
    protected function _getStoresFilter()
    {
        $enabledStores = $this->_currentStatus->getStoresFilter();

        if (count($enabledStores) == 0) {
        	return null;
        }

        $filterArray = array();

        foreach ($enabledStores as $store) {
            $filterArray []= array('eq' => $store);
        }

        return $filterArray;
    }

    /**
     *  Add customers full sync events to queue
     *  This method will return number of events that were added to queue.
     *  
     *  @return int
     */
    protected function _addCustomersToQueue() 
    {
        $addedEvents = 0;

        $lastCustomerId = $this->_currentStatus->getLastCustomerId();        

        $customersCollection = Mage::getModel('customer/customer')->getCollection();
        $customersCollection->addAttributeToSort('entity_id', 'ASC');
        $customersCollection->setPageSize($this->_eventsLimit)->addAttributeToFilter('entity_id', array('gt' => $lastCustomerId ));

        if ($filterArray = $this->_getStoresFilter()) { 
            $customersCollection->addAttributeToFilter('store_id', $filterArray);
        }

        foreach ($customersCollection as $customer) {
            $queue = Mage::getModel('marketingsoftware/queue_item')
                ->setObject(null)
                ->setCustomer($customer->getId())
                ->setAction('full')
                ->setName('customer')
                ->setEntityId($customer->getId())
                ->save();

            $this->_currentStatus->setLastCustomerId($customer->getEntityId());            

            $addedEvents++;
        }

        Mage::helper('marketingsoftware/config')->setCustomerProgressStatus(date('Y-m-d H:i:s'));

        return $addedEvents;
    }

    /**
     *  Add all orders that don't have a customer instance tied to them aka 'guest orders'
     *  
     *  @return int
     */
    protected function _addOrdersToQueue()
    {
        $addedEvents = 0;

        $ordersCollection = Mage::getModel('sales/order')->getCollection();
        $ordersCollection->addAttributeToFilter('entity_id', array (
            'gt' => $this->_currentStatus->getLastOrderId()
        ));

        if ($filterArray = $this->_getStoresFilter()) { 
            $ordersCollection->addAttributeToFilter('store_id', $filterArray);
        }

        $ordersCollection->addFieldToFilter('customer_id', array(
            'null' => true
        ));
        $ordersCollection->addAttributeToSort('entity_id', 'ASC');
        $ordersCollection->setPageSize($this->_eventsLimit);

        foreach ($ordersCollection as $order) {
            $queue = Mage::getModel('marketingsoftware/queue_item')
                ->setObject(array('customer' => null))
                ->setCustomer(null)
                ->setAction('modify')
                ->setName('order')
                ->setEntityId($order->getId())
                ->save();

            $addedEvents++;

            $this->_currentStatus->setLastOrderId($order->getEntityId());
        }
 
        Mage::helper('marketingsoftware/config')->setOrderProgressStatus(date('Y-m-d H:i:s'));

        return $addedEvents;
    }

    /**
     *  This method will spawn events that will synchronise subscriptions that 
     *  doen't have customer entity. This method will also return number of new 
     *  events spawned.
     *  
     *  @return int
     */
    protected function _addSubscriptionsToQueue() 
    {
        $addedEvents = 0;

        $subscriptionsCollection = Mage::getModel('newsletter/subscriber')->getCollection();
        $subscriptionsCollection->addFieldToFilter('subscriber_id', array(
            'gt' => $this->_currentStatus->getLastSubscriptionId()
        ));
        $subscriptionsCollection->setOrder('subscriber_id', 'ASC');
        $subscriptionsCollection->addFieldToFilter('customer_id', array(
            'eq' => 0, 
            'null' => true
        ));

        if ($filterArray = $this->_getStoresFilter()) { 
            $subscriptionsCollection->addFieldToFilter('store_id', $filterArray);
        }

        $subscriptionsCollection->setPageSize($this->_eventsLimit);

        foreach ($subscriptionsCollection as $subscription) {
            Mage::getModel('marketingsoftware/queue_item')
                ->setObject(null)
                ->setCustomer($subscription->getCustomerId())
                ->setAction('modify')
                ->setName('subscription')
                ->setEntityId($subscription->getId())
                ->save();

            $this->_currentStatus->setLastSubscriptionId($subscription->getId());

            $addedEvents++;
        }

        return $addedEvents;
    }
}