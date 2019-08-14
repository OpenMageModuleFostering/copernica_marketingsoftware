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
 * Observer object.  
 *
 */
class Copernica_MarketingSoftware_Model_Observer
{
    /**
     * Method for the following events:
     * 'checkout_controller_onepage_save_shipping_method'
     * 'checkout_controller_multishipping_shipping_post'
     * This method is fired during checkout process, after the customer has entered billing address
     * and saved the shipping method
     * @param $observer Varien_Event_Observer
     */
    public function checkoutSaveStep(Varien_Event_Observer $observer)
    {
        // if the plug-in is not enabled, skip this
        if (!$this->enabled()) return;

        // Do we have a valid item?
        if ($quote = $observer->getEvent()->getQuote())
        {
            // wrap the object
            $object = Mage::getModel('marketingsoftware/abstraction_quote')->setOriginal($quote);

            // This quote should be added to the queue
            $queue = Mage::getModel('marketingsoftware/queue')
                ->setObject($object)
                ->save();
        }
    }

    /**
     * Method for event 'sales_quote_item_delete_before'.
     * An item is removed from a quote
     */
    public function quoteItemRemoved(Varien_Event_Observer $observer)
    {
        // if the plug-in is not enabled, skip this
        if (!$this->enabled()) return;

        // Do we have a valid item?
        if ($item = $observer->getEvent()->getItem())
        {
            if ($item->getParentItemId()) {
                //this item has a parent so its data is already contained within the parent item
                return;
            }

            if (!$item->getQuote()->getCustomerId()) {
                //this item cannot be linked to a customer, so is not relevant at this moment
                return;
            }

            // wrap the object
            $object = Mage::getModel('marketingsoftware/abstraction_quote_item')->setOriginal($item);

            // This quote item should be added to the queue
            $queue = Mage::getModel('marketingsoftware/queue')
                ->setObject($object)
                ->setAction('remove')
                ->save();
        }
    }

    /**
     * Method for event 'sales_quote_item_save_after'.
     * An item is added or modified
     */
    public function quoteItemModified(Varien_Event_Observer $observer)
    {
        // if the plug-in is not enabled, skip this
        if (!$this->enabled()) return;

        // Do we have a valid item?
        if ($item = $observer->getEvent()->getItem())
        {
            // unfortunately hasDataChanges is only available in Mage 1.5 and up
            if (!$item->hasDataChanges() && method_exists($item, 'hasDataChanges')) {
                //an event is triggered every time the object is saved, even when nothing has changed
                //for example, when an item is added to the quote
                //however, the update date may have changed (even by 1 second) which will trigger a new queue item any way
                return;
            }

            if ($item->getParentItemId()) {
                //this item has a parent so its data is already contained within the parent item
                return;
            }

            if (!$item->getQuote()->getCustomerId()) {
                //this item cannot be linked to a customer, so is not relevant at this moment
                return;
            }

            // wrap the object
            $object = Mage::getModel('marketingsoftware/abstraction_quote_item')->setOriginal($item);

            // This quote item should be added to the queue
            $queue = Mage::getModel('marketingsoftware/queue')
                ->setObject($object)
                ->setAction($item->isObjectNew() ? 'add' : 'modify')
                ->save();
        }
    }

    /**
     * Method for event 'sales_order_save_after'.
     * An order is added or modified
     */
    public function orderModified(Varien_Event_Observer $observer)
    {
        // if the plug-in is not enabled, skip this
        if (!$this->enabled()) return;

        // Do we have a valid item?
        if ($order = $observer->getEvent()->getOrder())
        {
            if (!$order->getState()) {
                //if an order has no state, it will get one in the next call (usually a few seconds later)
                return;
            }

            // wrap the object
            $object = Mage::getModel('marketingsoftware/abstraction_order')->setOriginal($order);

            // This order should be added to the queue
            $queue = Mage::getModel('marketingsoftware/queue')
                ->setObject($object)
                ->setAction($order->isObjectNew() ? 'add' : 'modify')
                ->save();
        }
    }

    /**
     * Method for event 'newsletter_subscriber_delete_before'.
     * The newsletter subscription is deleted, do something with it,
     */
    public function newsletterSubscriptionRemoved(Varien_Event_Observer $observer)
    {
        // if the plug-in is not enabled, skip this
        if (!$this->enabled()) return;

        // Do we have a valid item?
        if ($subscriber = $observer->getEvent()->getSubscriber())
        {
            // wrap the object
            $object = Mage::getModel('marketingsoftware/abstraction_subscription')->setOriginal($subscriber);

            // This newsletter subscription should be added to the queue
            $queue = Mage::getModel('marketingsoftware/queue')
                ->setObject($object)
                ->setAction('remove')
                ->save();
        }
    }

    /**
     * Method for event 'newsletter_subscriber_save_after'.
     * The newsletter subscription is added or modified
     */
    public function newsletterSubscriptionModified(Varien_Event_Observer $observer)
    {
        // if the plug-in is not enabled, skip this
        if (!$this->enabled()) return;

        // Do we have a valid item?
        if ($subscriber = $observer->getEvent()->getSubscriber())
        {
            // unfortunately hasDataChanges is only available in Mage 1.5 and up
            if (!$subscriber->hasDataChanges() && method_exists($subscriber, 'hasDataChanges')) {
                // an event is triggered every time the object is saved, even when nothing has changed
                // for example, when an order is placed
                return;
            }

            // wrap the object
            $object = Mage::getModel('marketingsoftware/abstraction_subscription')->setOriginal($subscriber);

            // This newsletter subscription should be added to the queue
            $queue = Mage::getModel('marketingsoftware/queue')
                ->setObject($object)
                ->setAction($subscriber->isObjectNew() ? 'add' : 'modify')
                ->save();
        }
    }

    /**
     * Method for event 'customer_delete_before'.
     * The customer is deleted, do something with it,
     */
    public function customerRemoved(Varien_Event_Observer $observer)
    {
        // if the plug-in is not enabled, skip this
        if (!$this->enabled()) return;

        // Do we have a valid item?
        if ($customer = $observer->getEvent()->getCustomer())
        {
            // wrap the object
            $object = Mage::getModel('marketingsoftware/abstraction_customer')->setOriginal($customer);

            // This customer should be added to the queue
            $queue = Mage::getModel('marketingsoftware/queue')
                ->setObject($object)
                ->setAction('remove')
                ->save();
        }
    }

    /**
     * Method for event 'customer_save_after'.
     * The customer is added or modified, do something with it,
     */
    public function customerModified(Varien_Event_Observer $observer)
    {
        // if the plug-in is not enabled, skip this
        if (!$this->enabled()) return;

        // Do we have a valid item?
        if ($customer = $observer->getEvent()->getCustomer())
        {
            // wrap the object
            $object = Mage::getModel('marketingsoftware/abstraction_customer')->setOriginal($customer);

            // This customer should be added to the queue
            $queue = Mage::getModel('marketingsoftware/queue')
                ->setObject($object)
                ->setAction($customer->isObjectNew() ? 'add' : 'modify')
                ->save();
        }
    }

    /**
     * Is the Copernica module enabled?
     *  
     * @return boolean
     */
    protected function enabled()
    {
        // get the result from the helper
        return Mage::helper('marketingsoftware')->enabled();
    }

    /**
     * Process the queue. This function will stop running after 180 
     * seconds. It will then start processing the rest of the queue
     * the next time the cron event is fired.  
     * 
     */
    public function processQueue()
    {
        // get the config
        $config = Mage::helper('marketingsoftware/config');
        $config->setLastStartTimeCronjob(date("Y-m-d H:i:s"));
        
        // if the plug-in is not enabled, skip this
        if (!$this->enabled()) return;

        // Set the the time limit to a high number
        set_time_limit(0);
        $collection = Mage::getResourceModel('marketingsoftware/queue_collection')
            ->addDefaultOrder()->setPageSize(150);

        // store the start time
        $time = time();

        // are there any items?
        if (count($collection) == 0) 
        {
            // set some debug info
            $config->setLastCronjobProcessedTasks(0);
            $config->setLastEndTimeCronjob(date("Y-m-d H:i:s"));
            return true;
        }
        
        try {
            // Perform some basic checks first
            Mage::getSingleton('marketingsoftware/marketingsoftware')->api()->check(true);
        } catch (CopernicaError $e) {
            // log the message
            Mage::log("Copernica/marketingSoftware: ".(string)$e);

            // Do not process any records
            return;
        } catch(Exception $e) {
            Mage::logException($e);
            return;
        }
        
        // get the number of processed iterations
        $processedTasks = 0;
        
        // iterate over the collection
        foreach ($collection as $queue)
        {
            // Is the timer already expired
            if (time() > ($time + 3 * 60)) break;

            try
            {
                // we still have time, so lets process an item from the queue
                $success = $queue->process();

                // the processing is successful, remove the item from the queue
                if ($success) 
                {
                    // increment the counter for the processed tasks
                    $processedTasks++;
                    
                    // also delete the item from the queue
                    $queue->delete();
                }
            }
            catch(Exception $e)
            {
                // If its not an Copernica Error than get the message
                if ($e instanceOf CopernicaError)
                {
                    // assign the message
                    $message = (string)$e;
                }
                else
                {
                    // Log the message and the exception
                    $message = '['.get_class($e).'] '.$e->getMessage();

                    // Write the error to the log
                    Mage::logException($e);
                }

                // store the log message
                Mage::log($message);

                // write the error to the database and store the time of the error
                $queue->setResult($message)
                    ->setResultTime(date('Y-m-d H:i:s'))
                    ->save();
            }
        }

        // Finished
        $config->setLastCronjobProcessedTasks($processedTasks);
        $config->setLastEndTimeCronjob(date("Y-m-d H:i:s"));
    }
}