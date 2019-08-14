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
 *  This class will write all relevant for us data to files. This way Copernica
 *  employees can sync the data withing short time inserting all data directly
 *  into servers rather than dealing with REST API.
 */
class Copernica_MarketingSoftware_Model_QueueEvent_FileSync extends Copernica_MarketingSoftware_Model_QueueEvent_Abstract
{
    /**
     *  How many entities we want to sync.
     *  By default we will sync 50000 entites at once.
     *  @var    int
     */
    private $writesLimit = 50000;

    /**
     *  Upper time limit that will be used to limit number of entities that will
     *  be writted to file. By default we are stting it to 4 minutes (240 seconds).
     *  @var    int
     */
    private $timeLimit = 240;

    /**
     *  Time in seconds when we did start form unix epoch. 1 > x > 0 part represetns 
     *  nearest micorsecond.
     *  @var    float
     */
    private $startTime;

    /**
     *  Current event status.
     */
    private $currentStatus;

    /**
     *  Process event.
     *  @return boolean
     */
    public function process() 
    {
        // initialize event to be processed
        $this->init();

        // event should be respawned?
        $shouldRespawn = false;

        try {
            // write customers data
            if($this->writeCustomersData() == $this->writesLimit) {
                // respawn this event cause we have something to do
                $this->respawn();
                
                // we are done here            
                return true;  
            } 

            // some of the orders can not have a valid customer instance tied to them
            // we want to sync them also.
            if($this->writeOrdersData() == $this->writesLimit){
                // respawn this event cause we have somethid to do
                $this->respawn();  

                // we are done here
                return true;
            } 

            // write subscribers data. Some of the subscribers could not be customers.
            // we want to sync them also.
            if ($this->writeSubscribersData() == $this->writesLimit) {
                // respawn this event cause we have something to do
                $this->respawn();

                // we are done here
                return true;
            }
        } 
        // we are throwing an exception only in one case. When the time limit is 
        // reached. In that case we should stop processing entities and close this 
        // event.
        catch (Exception $exception) {
            // nothing special to do really. All data will be closed automatically.

            // if we did reached time limit that means we really should respawn 
            // the event on event queue
            $this->respawn();
        }

        // everything went just dandy
        return true;
    }

    /**
     *  This method will prepare event to be processed.
     */
    private function init()
    {
        // get sync status object
        $this->currentStatus = $this->getObject();

        // if sum of last processed ids is then we can assume that we are doing 
        // a new sync. So we want to clear old data files so we can make new ones.
        // if ($this->currentStatus->getLastCustomerId() == 0 
        //     && $this->currentStatus->getLastOrderId() == 0
        //     && $this->currentStatus->getLastSubscriptionId() == 0
        // )
        //     Mage::helper('marketingsoftware/DataWriter')->clearDataFiles();

        // store start time for this event
        $this->startTime = microtime(true);
    }

    /**
     *  This event has an internal timer that will keep track how much time left 
     *  we have to process entities. This method will tell us if we are reaching 
     *  that time or not.
     *  @return boolean
     */
    private function isTimeLimitReached() 
    {
        return $this->timeLimit < (microtime(true) - $this->startTime);
    }

    /**
     *  Write customers data to files.
     *  @return int     Number of written customers
     */
    private function writeCustomersData()
    {
        // counter that will report how many events we did added
        $addedCustomers = 0;

        // get customeres collection
        $customersCollection = Mage::getModel('customer/customer')->getCollection();

        // we want to get the collection in certain order
        $customersCollection->addAttributeToSort('entity_id', 'ASC');

        // set the customers collection
        $customersCollection->addAttributeToFilter('entity_id', array('gt' => $this->currentStatus->getLastCustomerId() ));

        // set the customers collection
        $customersCollection->setPageSize($this->writesLimit);

        // iterate over all customers in collection
        foreach ($customersCollection as $customer) {
            // write customer data
            $this->writeCustomer($customer);

            // set new last customer Id
            $this->currentStatus->setLastCustomerId($customer->getEntityId());

            // increase events counter
            $addedCustomers++;

            // if we are reaching time limit we should stop processing 
            if ($this->isTimeLimitReached()) {
               throw new Exception('Time limit reached');
            }
        }

        // return number of written customers
        return $addedCustomers;
    }

    /**
     *  Write orders data to files.
     *  @return int     Number of orders written to file
     */
    private function writeOrdersData()
    {
        // counter that will remember number of orders that we did write
        $addedOrders = 0;

        // get collection of orders
        $ordersCollection = Mage::getModel('sales/order')->getCollection();

        // we want orders that do not have a proper customer instance
        $ordersCollection->addFieldToFilter('customer_id', array(
            'eq' => 0, 
            'null' => true
        ));

        // we want to pick up from where we stoped
        $ordersCollection->addAttributeToFilter('entity_id', array (
            'gt' => $this->currentStatus->getLastOrderId()
        ));

        // we want to get collection in certain order
        $ordersCollection->addAttributeToSort('entity_id', 'ASC');

        // set collections page size to write limit
        $ordersCollection->setPageSize($this->writesLimit);

        // iterate over all orders and write them to data file
        foreach ($ordersCollection as $order)
        {
            // write order
            $this->writeOrder($order);

            // increase order counter
            $addedOrders++;

            // remember what was last order Id
            $this->currentStatus->setLastOrderId($order->getEntityId());

            // if we are reaching time limit we should stop processing 
            if ($this->isTimeLimitReached()) {
               throw new Exception('Time limit reached');
            }
        }

        // return number of orders that we added to data file
        return $addedOrders;
    }

    /**
     *  Wrtie subscribers to files.
     *  @return int     Number of subscribers written to file.
     */
    private function writeSubscribersData()
    {   
        // subscribers counter
        $addedSubs = 0;

        // get subscribers collection
        $subscribersCollection = Mage::getModel('newsletter/subscriber')->getCollection();

        // we only want subscribers that are not customers
        $subscribersCollection->addFieldToFilter('customer_id', array(
            'eq' => 0,
            'null' => true
        ));

        // we want to pick up from where we did stop last time
        $subscribersCollection->addFieldToFilter('subscriber_id', array(
            'gt' => $this->currentStatus->getLastSubscriptionId()
        ));

        // we want to get sorted collection
        $subscribersCollection->setOrder('subscriber_id', 'ASC');

        // cap collection with current writes limit
        $subscribersCollection->setPageSize($this->writesLimit);

        // iterate over all subscribers and write such profiles to data files
        foreach ($subscribersCollection as $subscriber) {
            // write subscriber
            $this->writeSubscriber($subscriber);

            // increament counter
            $addedSubs++;

            // store last subscription Id
            $this->currentStatus->setLastSubscriptionId($subscriber->getId());

            // if we are reaching time limit we should stop processing 
            if ($this->isTimeLimitReached()) {
               throw new Exception('Time limit reached');
            }
        }

        // return amount of subscribers that we add
        return $addedSubs;
    }

    /**
     *  This method will respawn event. This way we can write all data to files 
     *  and not freeze server for longer period of time.
     */
    private function respawn()
    {
        // create new event at the end of the queue
        Mage::getModel('marketingsoftware/queue')
            ->setObject($this->currentStatus)
            ->setAction('file_sync')
            ->save();
    }

    /**
     *  Write profile data
     *  @param 
     */
    private function writeCustomer($customer)
    {
        // get data writer inside local scope
        $dataWriter = Mage::helper('marketingsoftware/DataWriter');
                
        // create customer copernica abstract
        $customerAbstract = Mage::getModel('marketingsoftware/abstraction_customer')->loadCustomer($customer->getEntityId());

        // create customer data
        $customerProfile = Mage::getModel('marketingsoftware/copernica_profilecustomer')->setCustomer($customerAbstract);
        $customerData = $customerProfile->toArray();

        // specify orders section for customer
        $orders = array();

        // get orders that belong to current customer
        $ordersCollection = Mage::getModel('sales/order')->getCollection();
        $ordersCollection->addFieldToFilter('customer_id', array('eq' => $customer->getId()));

        // iterate over all customers order and get data for them
        foreach ($ordersCollection as $order) $orders[] = $this->getOrderData($order);

        // assign orders to customer data
        $customerData['orders'] = $orders;

        // placeholder for customer addresses
        $addresses = array();

        // iterate over all customers addresses and get data for them
        foreach ($customer->getAddresses() as $address) $addresses[] = $this->getAddressData($address);

        // assign addresses data to customer data
        $customerData['addresses'] = $addresses;

        // write customer to data files
        $dataWriter->storeProfile($customerData);
    }

    /**
     *  This method will write order as a profile. It should be done only on orders 
     *  that do not have a proper customer instance (it was removed or it never 
     *  existed like with anonymous orders). For orders that do have a proper 
     *  customer instance it will corrupt data since data insert will not be able 
     *  to upgrade customer_id field.
     *  @param  Mage_Sales_Model_Order
     */
    private function writeOrder($order)
    {
        // get order profile assoc data that we can use in json encoder
        $orderAbstract = Mage::getModel('marketingsoftware/abstraction_order')->setOriginal($order);
        $orderProfile = Mage::getModel('marketingsoftware/copernica_profileorder')->setOrder($orderAbstract);
        $profileData = $orderProfile->toArray();

        // we will need order data so we can build a proper profile
        $orderSubprofile = Mage::getModel('marketingsoftware/copernica_Order_Subprofile')->setOrder($orderAbstract);
        $orderData = $orderSubprofile->toArray();

        // placeholder for items
        $items = array();

        // iterate over all item and get theirs data
        foreach ($order->getItemsCollection() as $item) {
            $items[] = $this->getItemData($item);
        }

        // assign items data to order data
        $orderData['items'] = $items;

        // set orders to progile
        $profileData['orders'] = array( $orderData );

        // write profile to data files
        Mage::helper('marketingsoftware/DataWriter')->storeProfile($profileData);
    }

    /**
     *  Get order data array that can be passed to json encoder. This way we can
     *  transfer data in form of objects and arrays.
     *  @param  Mage_Sales_Model_Order
     *  @return assoc
     */
    private function getOrderData($order)
    {
        // get order subprofile instance        
        $orderAbstract = Mage::getModel('marketingsoftware/abstraction_order')->importFromOriginal($order);
        $orderSubprofile = Mage::getModel('marketingsoftware/Copernica_Order_Subprofile')->setOrder($orderAbstract);

        // get order data array
        $orderData = $orderSubprofile->toArray();

        // placeholder for items array
        $items = array();

        /**
         *  Order has 3 (or more) methods to get set of items. ::getAllItems(),
         *  ::getAllVisibleItems() and ::getItemsCollection(). ::getAllItems()
         *  and getAllVisibleItems() returns an array and items have some missing
         *  data (or are just populated in odd way). ::getAllVisibleItems() seems 
         *  to return all orders configurable items, so top-level items.
         *  ::getItemsCollection() seems to fetch proper items collection that 
         *  we can use.
         *  It's nice of magento to provide such friendly interface.
         */
        $itemsCollection = $order->getItemsCollection();

        // iterate over all items 
        foreach ($itemsCollection as $item) $items[] = $this->getItemData($item);

        // add items to order data
        $orderData['items'] = $items;

        // return order data array
        return $orderData;
    }

    /**
     *  Get assoc array of order item.
     *  @param  Mage_Sales_Model_Order_Item
     *  @return assoc
     */
    private function getItemData($item)
    {
        // get order item data array.
        $itemAbstract = Mage::getModel('marketingsoftware/abstraction_Order_Item')->setOriginal($item);
        $itemSubprofile = Mage::getModel('marketingsoftware/Copernica_Orderitem_Subprofile')->setOrderItem($itemAbstract);

        // return assoc array that can be passed to json encoder
        return $itemSubprofile->toArray();
    }

    /**
     *  Get address data from Magento raw object.
     *  @param  Mage_Customer_Model_Address
     *  @return assoc
     */
    private function getAddressData($address)
    {
        // get Copernica instance of address subprofile that we can use in JSON 
        // file. 
        $addressAbstract = Mage::getModel('marketingsoftware/abstraction_address')->setOriginal($address);
        $addressSubprofile = Mage::getModel('marketingsoftware/copernica_Address_Subprofile')->setAddress($addressAbstract);

        // return address subprofile data
        return $addressSubprofile->toArray();
    }

    /**
     *  This function will write subscriber to data files
     *  @param  Mage_Newsletter_Model_Subscriber
     */
    private function writeSubscriber($subscriber)
    {
        // get subscriber profile so we can use it to get assoc array to write 
        // to JSON data files
        $subscriberAbstract = Mage::getModel('marketingsoftware/abstraction_Subscription')->setOriginal($subscriber);
        $subscriberProfile = Mage::getModel('marketingsoftware/copernica_Profilesubscription')->setSubscription($subscriberAbstract);

        // store subscriber data in data files
        Mage::helper('marketingsoftware/DataWriter')->storeProfile($subscriberProfile->toArray());
    }
}