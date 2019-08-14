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
 *  A wrapper object around an event
 */
class Copernica_MarketingSoftware_Model_QueueEvent_StartSync extends Copernica_MarketingSoftware_Model_QueueEvent_Abstract
{
    /**
     *  @var integer store the start time
     */
    protected $startTime;

    /**
     *  @var integer store the time limit
     */
    protected $timeLimit;


    /**
     *  Process this item in the queue
     *  @return boolean was the processing successfull
     */
    public function process()
    {
        // store the start time
        $this->startTime = time();

        // store the time limit
        $this->timeLimit = 3*60;

        // A page size to limit the data in memory
        $pageSize = 20;

        // add all the customers to the queue
        if (!$this->_addCustomersToQueue($pageSize)) return false;

        // add all the orders (which don't have a customer) to the queue
        if (!$this->_addOrdersToQueue($pageSize)) return false;

        // add the newsletter subscriptions to the queue
        if (!$this->_addSubscriptionsToQueue($pageSize)) return false;

        // It succeeded
        return true;
    }

    /**
     *  Add all the customers to the queue
     *  this piece of code has been improved by Cream (www.cream.nl)
     *  @param integer  page size
     */
    protected function _addCustomersToQueue($pageSize)
    {
        // Get the config helper
        $config = Mage::helper('marketingsoftware/config');

        // get the customers,
        // this piece of code has been improved by Cream (www.cream.nl)
        $customers = Mage::getModel('customer/customer')
            ->getCollection()
            ->setPageSize($pageSize)
            ->addAttributeToSort('updated_at')
            ->addAttributeToFilter('store_id', array('notnull' => true))
            ->addAttributeToFilter('website_id', array('notnull' => true))
            ->addAttributeToFilter('updated_at', array(
                'from' => $config->getCustomerProgressStatus()
            ));

        // Get the id of last customer which has been processed via this synchronisation
        $progressDateTime = $config->getCustomerProgressStatus();

        // iterate over the pages with customers
        for ($page = 1; $page <= $customers->getLastPageNumber(); $page++)
        {
            // load the data for this page
            $customers->setPage($page, $pageSize)->load();

            // iterate over the customers
            foreach ($customers as $customer)
            {
                // Was this record changed or modified after the last synchronisation
                if ($customer->getCreatedAt() < $progressDateTime &&
                    $customer->getUpdatedAt() < $progressDateTime ) continue;

                // wrap the object
                $object = Mage::getModel('marketingsoftware/abstraction_customer')->loadCustomer($customer->getEntityId());

                // This customer should be added to the queue
                $queue = Mage::getModel('marketingsoftware/queue')
                    ->setObject($object)
                    ->setAction('full')
                    ->save();

                // Get the maximum progress time
                $progressDateTime = max($progressDateTime, $customer->getUpdatedAt());

                // get rid of the customer and the object
                unset($customer);
                unset($object);
            }

            // Store the progress status
            $config->setCustomerProgressStatus($progressDateTime);

            // Clear the cached customers
            $customers->clear();

            // Did we already spend to long on processing the records
            if (time() > ($this->startTime + $this->timeLimit)) return false;
        }
        // clear the customers variable
        unset($customers);

        // Store the progress status
        $config->setCustomerProgressStatus(date('Y-m-d H:i:s'));

        // we did complete
        return true;
    }

    /**
     *  Add all the orders to the queue, which are placed by guest subscribers
     *  this piece of code has been improved by Cream (www.cream.nl)
     *  @param integer  page size
     */
    protected function _addOrdersToQueue($pageSize)
    {
        // Get the config helper
        $config = Mage::helper('marketingsoftware/config');

        // get the orders, which don't have a customer_id
        // this piece of code has been improved by Cream (www.cream.nl)
        $orders = Mage::getModel('sales/order')
                    ->getCollection()
                    ->addAttributeToSort('updated_at')
                    ->addAttributeToFilter('store_id', array('notnull' => true))
                    ->addAttributeToFilter('updated_at', array(
                        'from' => $config->getOrderProgressStatus()
                    ));

        // The add field to search filter is not supported in Magento 1.4
        //if (is_callable(array($orders, 'addFieldToSearchFilter')))
        //{
        //    $orders->addFieldToSearchFilter('customer_id', 0)
        //            ->addFieldToSearchFilter('customer_id', array('null' => 1));
        //}

        // Set the page size
        $orders->setPageSize($pageSize);

        // Get the id of last order which has been processed via this synchronisation
        $progressDateTime = $config->getOrderProgressStatus();

        // iterate over the pages with orders
        for ($page = 1; $page <= $orders->getLastPageNumber(); $page++)
        {
            // load the data for this page
            $orders->setPage($page, $pageSize)->load();

            // iterate over the orders
            foreach ($orders as $order)
            {
                // Was this record changed or modified after the last synchronisation
                if ($order->getCreatedAt() < $progressDateTime &&
                    $order->getUpdatedAt() < $progressDateTime ) continue;
                
                // Get the maximum progress time
                $progressDateTime = max($progressDateTime, $order->getUpdatedAt());
                
                // Only sync guest orders
                if ($order->getCustomerId()) continue;                

                // wrap the object
                $object = Mage::getModel('marketingsoftware/abstraction_order')->loadOrder($order->getEntityId());

                // This order should be added to the queue
                $queue = Mage::getModel('marketingsoftware/queue')
                    ->setObject($object)
                    ->save();

                // get rid of the order and the object
                unset($order);
                unset($object);
            }

            // Store the progress status
            $config->setOrderProgressStatus($progressDateTime);

            // Clear the cached orders
            $orders->clear();

            // Did we already spend to long on processing the records
            if (time() > ($this->startTime + $this->timeLimit)) return false;
        }

        // clear the orders variable
        unset($orders);

        // Store the progress status
        $config->setOrderProgressStatus(date('Y-m-d H:i:s'));

        // we did complete
        return true;
    }

    /**
     *  Add all the subscriptions to the queue, which are placed by guest subscribers
     *  @param integer  page size
     */
    protected function _addSubscriptionsToQueue($pageSize)
    {
        // Get the config helper
        $config = Mage::helper('marketingsoftware/config');

        // get the subscriptions
        $subscriptions = Mage::getModel('newsletter/subscriber')
                            ->getCollection()
                            ->addFieldToFilter('customer_id', array(0, 'null'))
                            ->addFieldToFilter('store_id', array('notnull' => true));

        // iterate over the subscriptions
        foreach ($subscriptions as $subscription)
        {
            // wrap the object
            $object = Mage::getModel('marketingsoftware/abstraction_subscription')
                        ->setOriginal($subscription);

            // This subscription should be added to the queue
            $queue = Mage::getModel('marketingsoftware/queue')
                    ->setObject($object)
                    ->save();

            // get rid of the subscription and the object
            unset($subscription);
            unset($object);
        }

        // clear the subscriptions variable
        unset($subscriptions);

        // we did complete
        return true;
    }
}