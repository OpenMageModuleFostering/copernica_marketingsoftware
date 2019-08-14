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
class Copernica_MarketingSoftware_Model_QueueEvent_StartSync extends Copernica_MarketingSoftware_Model_QueueEvent_Abstract
{
    /** 
     *  How many events we want to spawn for each of customers/orders/subscriptions
     *  @var  int
     */
    private $eventsLimit = 5000;

    /**
     *  Current event status. It contains informations about last entites that were
     *  scheduled for synchronization. Also it does contain stores id that should
     *  be used to filter entites results.
     *  @var Copernica_MarketingSoftware_Model_SyncStatus
     */
    private $currentStatus;

    /** 
     *  Process event. If not all entities can be added to synchtonization this
     *  event will respawn with new set of data. Note that this event will be 
     *  respawning till all entities will be scheduled to sync.
     *  @return boolean     Did we process this event without any errors? 
     */
    public function process() {
        // get sync status object
        $this->currentStatus = Copernica_MarketingSoftware_Model_SyncStatus::fromStd($this->getObject());

        // flag to tell us if we should respawn the event
        $shouldRespawn = false;

        // we want to add customers events to queue
        if ($this->addCustomersToQueue() == $this->eventsLimit) $shouldRespawn = true;

        // we want to add orders events to queue
        if ($this->addOrdersToQueue() == $this->eventsLimit) $shouldRespawn = true;

        // we want to add subscriptions to queue
        if ($this->addSubscriptionsToQueue() == $this->eventsLimit) $shouldRespawn = true;

        // respawn the event
        if ($shouldRespawn) $this->respawn();

        // we did process the event with success
        return true;
    }

    /**
     *  Respawn start sync event.
     */
    public function respawn()
    {
        // we want to recreate start sync task with current status object
        $queue = Mage::getModel('marketingsoftware/queue')
            ->setObject($this->currentStatus->toArray())
            ->setAction('start_sync')
            ->setName('startSync')
            ->save();

        // we should be just fine here
        return true;
    }

    /**
     *  Get ::addAttributeToFilter() compatible array of stores that we want to
     *  sync. Null will be returned when there is no stores to filter.
     *  @return array|null
     */
    private function getStoresFilter()
    {
        // get enabled stores array
        $enabledStores = $this->currentStatus->getStoresFilter();

        // do we have stores to create a filter?
        if (count($enabledStores) == 0) return null;

        // placeholder for filter array
        $filterArray = array();

        // iterate over all stores and add them to filter array
        foreach ($enabledStores as $store) 
        {
            $filterArray []= array('eq' => $store);
        }

        // return filter array
        return $filterArray;
    }

    /**
     *  Add customers full sync events to queue
     *  This method will return number of events that were added to queue.
     *  @return int
     */
    private function addCustomersToQueue() 
    {
        // counter that will report how many events we did add
        $addedEvents = 0;

        // get last customer id
        $lastCustomerId = $this->currentStatus->getLastCustomerId();

        // get customers collection
        $customersCollection = Mage::getModel('customer/customer')->getCollection();

        // we want to get collection in certain order
        $customersCollection->addAttributeToSort('entity_id', 'ASC');

        // set the customers collection
        $customersCollection->setPageSize($this->eventsLimit)->addAttributeToFilter('entity_id', array('gt' => $lastCustomerId ));

        // check if we should filter out some stores
        if ($filterArray = $this->getStoresFilter()) 
            $customersCollection->addAttributeToFilter('store_id', $filterArray);

        // iterate over customers collection
        foreach ($customersCollection as $customer) {

            // create new event on queue
            $queue = Mage::getModel('marketingsoftware/queue')
                ->setObject(null)
                ->setCustomer($customer->getId())
                ->setAction('full')
                ->setName('customer')
                ->setEntityId($customer->getId())
                ->save();

            // set new last customer Id
            $this->currentStatus->setLastCustomerId($customer->getEntityId());

            // increase events counter
            $addedEvents++;
        }

        // store when we did last update customers
        Mage::helper('marketingsoftware/config')->setCustomerProgressStatus(date('Y-m-d H:i:s'));

        // return counter
        return $addedEvents;
    }

    /**
     *  Add all orders that don't have a customer instance tied to them aka 'guest orders'
     *  @return int
     */
    private function addOrdersToQueue()
    {
        // new events counter
        $addedEvents = 0;

        // get orders collection
        $ordersCollection = Mage::getModel('sales/order')->getCollection();

        // we want to pick up from where we stoped
        $ordersCollection->addAttributeToFilter('entity_id', array (
            'gt' => $this->currentStatus->getLastOrderId()
        ));

        // check if we should filter out some stores
        if ($filterArray = $this->getStoresFilter()) 
            $ordersCollection->addAttributeToFilter('store_id', $filterArray);

        // we want orders that don't have a customer
        $ordersCollection->addFieldToFilter('customer_id', array(
            'null' => true
        ));

        // we want to get collection in certain order
        $ordersCollection->addAttributeToSort('entity_id', 'ASC');

        // set limit on collection
        $ordersCollection->setPageSize($this->eventsLimit);

        // iterate over all orders in collection
        foreach ($ordersCollection as $order) {

            // create new event on queue
            $queue = Mage::getModel('marketingsoftware/queue')
                ->setObject(array('customer' => null))
                ->setCustomer(null)
                ->setAction('modify')
                ->setName('order')
                ->setEntityId($order->getId())
                ->save();

            // increase events counter
            $addedEvents++;

            // set last order Id
            $this->currentStatus->setLastOrderId($order->getEntityId());
        }

        // store when we did last time update orders 
        Mage::helper('marketingsoftware/config')->setOrderProgressStatus(date('Y-m-d H:i:s'));

        // return added events counter
        return $addedEvents;
    }

    /**
     *  This method will spawn events that will synchronise subscriptions that 
     *  doen't have customer entity. This method will also return number of new 
     *  events spawned.
     *  @return int
     */
    private function addSubscriptionsToQueue() 
    {
        // this will be the event count
        $addedEvents = 0;

        // get subscribers collection
        $subscriptionsCollection = Mage::getModel('newsletter/subscriber')->getCollection();

        // we want to pick up from where we did stop last time
        $subscriptionsCollection->addFieldToFilter('subscriber_id', array(
            'gt' => $this->currentStatus->getLastSubscriptionId()
        ));

        /*
         *  As it occurs that, it's too hard to make one interface for all collections
         *  in magento platform. Subscriptions is an non eav collection, so ::setOrder()
         *  is the same ass ::addFieldToSort() on eav collections.
         */
        $subscriptionsCollection->setOrder('subscriber_id', 'ASC');

        // we want subscribers that don't have a customer
        $subscriptionsCollection->addFieldToFilter('customer_id', array(
            'eq' => 0, 
            'null' => true
        ));

        // check if we should filter out some stores
        if ($filterArray = $this->getStoresFilter()) 
            $subscriptionsCollection->addFieldToFilter('store_id', $filterArray);

        // set a limit on collection
        $subscriptionsCollection->setPageSize($this->eventsLimit);

        // iterate over all subscriptions
        foreach ($subscriptionsCollection as $subscription) 
        {
            // create new event on queue
            Mage::getModel('marketingsoftware/queue')
                ->setObject(null)
                ->setCustomer($subscription->getCustomerId())
                ->setAction('modify')
                ->setName('subscription')
                ->setEntityId($subscription->getId())
                ->save();

            // store last subscriber Id
            $this->currentStatus->setLastSubscriptionId($subscription->getId());

            // increase events counter
            $addedEvents++;
        }

        // return count of events that we did add
        return $addedEvents;
    }
}