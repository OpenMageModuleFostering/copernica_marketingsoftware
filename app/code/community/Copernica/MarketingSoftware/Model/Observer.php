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
 */
class Copernica_MarketingSoftware_Model_Observer
{
    /**
     *  Check if event is added in store that we want to sync.
     *  @return bool
     */
    protected function isValidStore()
    {
        // get current store Id
        $currentStoreId = Mage::app()->getStore()->getId();

        // check if current store is enabled stores
        return Mage::helper('marketingsoftware/config')->isEnabledStore($currentStoreId);
    }

    /**
     * Method for the following events:
     * 'checkout_controller_onepage_save_shipping_method'
     * 'checkout_controller_multishipping_shipping_post'
     * This method is fired during checkout process, after the customer has entered billing address
     * and saved the shipping method
     * @param Varien_Event_Observer
     */
    public function checkoutSaveStep(Varien_Event_Observer $observer)
    {
        // if the plug-in is not enabled, skip this
        if (!$this->enabled() || !$this->isValidStore()) return;

        // Do we have a valid item?
        if (!is_object($quote = $observer->getEvent()->getQuote())) return;

        // get customer Id
        $customerId = $quote->getCustomerId();

        // if we don't have a customer this event is useless
        if (!$customerId) return;

        // get order
        $order = $quote->getOrder();

        // There is a slight difference between this event and order add/modify.
        // We have to clean up cart items.
        Mage::getModel('marketingsoftware/queue')
            ->setObject(array('customer' => $customerId))
            ->setName('checkout')
            ->setAction('add')
            ->setEntityId($quote->getId())
            ->setCustomer($customerId)
            ->save();
    }

    /**
     *  Method for event 'sales_quote_item_delete_before'.
     *  An item is removed from a quote
     *  @param  Varien_Event_Observer
     */
    public function quoteItemRemoved(Varien_Event_Observer $observer)
    {
        // if the plug-in is not enabled, skip this
        if (!$this->enabled() || !$this->isValidStore()) return;

        // Do we have a valid item?
        if (!is_object($item = $observer->getEvent()->getItem())) return;

        // if this item has a parent, it's data is already container within
        // parent item
        if ($item->getParentItemId()) return;

        // get quote into local scope
        $quote = $item->getQuote();

        // get customer Id
        $customerId  = $quote->getCustomerId();

        // cehck if we have customer
        if (!$customerId) return;

        /*
         *  Since this item is about to be removed from magento system we will have to
         *  fetch all data right now and pass that data to queue event instance.
         */
        $item = new Copernica_MarketingSoftware_Model_Copernica_Entity_CartItem($item);
        $itemData = $item->getREST()->getCartSubprofileData($quote->getId());
        $itemData['status'] = 'deleted';

        // This quote item should be added to the queue
        $queue = Mage::getModel('marketingsoftware/queue')
            ->setObject(array('quote' => $quote->getId(), 'customer' => $customerId, 'item' => $itemData))
            ->setCustomer($customerId)
            ->setAction('remove')
            ->setName('item')
            ->setEntityId($item->getId())
            ->save();
    }

    /**
     *  Method for event 'sales_quote_item_save_after'.
     *  An item is added or modified
     *  @param  Varien_Event_Observer
     */
    public function quoteItemModified(Varien_Event_Observer $observer)
    {
        // if the plug-in is not enabled, skip this
        if (!$this->enabled() || !$this->isValidStore()) return;

        // Do we have a valid item?
        if (!is_object($item = $observer->getEvent()->getItem())) return;

        // unfortunately hasDataChanges is only available in Mage 1.5 and up
        if (method_exists($item, 'hasDataChanges') && !$item->hasDataChanges()) {
            //an event is triggered every time the object is saved, even when nothing has changed
            //for example, when an item is added to the quote
            //however, the update date may have changed (even by 1 second) which will trigger a new queue item any way
            return;
        }

        // if this item has a prent item, this item will also be synced during parent item sync
        if ($item->getParentItemId()) return;

        // get quote
        $quote = $item->getQuote();

        // get customer Id
        $customerId = $quote->getCustomerId();

        /**
         *  Funny fakt: Magento does not always set customer id on quote instance.
         *  For example when quote was not finalized. Thus we will check the 
         *  session user to get customer Id.
         */
        if (!$customerId) 
        {
            // check if there is a user that is logged in
            if (!Mage::getSingleton('customer/session')->isLoggedIn()) return;

            // assign new user id
            $customerId = Mage::getSingleton('customer/session')->getCustomer()->getId();

            // well, a nasty error that we can not do anything about it
            if (!$customerId) return;
        }

        // This quote item should be added to the queue
        $queue = Mage::getModel('marketingsoftware/queue')
            ->setObject(array('quote' => $quote->getId(), 'customer' => $customerId))
            ->setCustomer($customerId)
            ->setAction($item->isObjectNew() ? 'add' : 'modify')
            ->setName('item')
            ->setEntityId($item->getId())
            ->save();
    }

    /**
     *  Listen to when quote is removed.
     *  @todo review this one
     *  @param Varien_Event_Observer
     */
    public function quoteDelete(Varien_Event_Observer $observer)
    {
        // if the extension is not enabled, skip this event
        if (!$this->enabled() || !$this->isValidStore()) return;

        // check if we do have a quote instance
        if (!is_object($quote = $observer->getEvent()->getQuote())) return;

        // get customer Id
        $customerId = $quote->getCustomerId();

        // try to get email address from available data
        $email = $quote->getCustomerEmail();
        $email = $email ? $email : $quote->getBillingAddress()->getEmail();
        $email = $email ? $email : $quote->getShippingAddress()->getEmail();

        // get objects related to store
        $store = $quote->getStore();
        $website = $store->getWebsite();
        $group = $store->getGroup();

        // construct store view identifier
        $storeView = implode(' > ', array ($website->getName(), $group->getName(), $store->getName()));

        /*
         *  If we don't have an email address it means that we can not pinpoint
         *  a profile in copernica, so basically we can not do anything useful
         *  with given quote. Thus, don't even create a queue event for it.
         */
        if ($email) Mage::getModel('marketingsoftware/queue')
            ->setObject(array('customer' => array(
                'id' => $customerId,
                'storeView' => $storeView,
                'email' => $email
            )))
            ->setCustomer($customerId)
            ->setAction('remove')
            ->serName('quote')
            ->setEntityId($quote->getId())
            ->save();
    }

    /**
     * Method for event 'sales_order_save_after'.
     * An order is added or modified
     */
    public function orderModified(Varien_Event_Observer $observer)
    {
        // if the plug-in is not enabled, skip this
        if (!$this->enabled() || !$this->isValidStore()) return;

        // Do we have a valid item?
        if (!is_object($order = $observer->getEvent()->getOrder())) return;

        // if an order has no state, it will get one in the next call (usually a few seconds later)
        if (!$order->getState()) return;

        // get customer Id
        $customerId = $order->getCustomerId();

        // This order should be added to the queue
        $queue = Mage::getModel('marketingsoftware/queue')
            ->setObject(array('customer' => $customerId))
            ->setCustomer($customerId)
            ->setAction($order->isObjectNew() ? 'add' : 'modify')
            ->setName('order')
            ->setEntityId($order->getEntityId())
            ->save();
    }

    /**
     * Method for event 'newsletter_subscriber_delete_before'.
     * The newsletter subscription is deleted, do something with it,
     */
    public function newsletterSubscriptionRemoved(Varien_Event_Observer $observer)
    {
        // if the plug-in is not enabled, skip this
        if (!$this->enabled() || !$this->isValidStore()) return;

        // Do we have a valid item?
        if (!is_object($subscriber = $observer->getEvent()->getSubscriber())) return;

        // get customer Id
        $customerId = $subscriber->getCustomerId();

        // This newsletter subscription should be added to the queue
        $queue = Mage::getModel('marketingsoftware/queue')
            ->setObject(array('store_id' => $subscriber->getStoreId(), 'email' => $subscriber->getSubscriberEmail()))
            ->setCustomer($customerId ? $customerId : null)
            ->setAction('remove')
            ->setName('subscription')
            ->setEntityId($subscriber->getId())
            ->save();
    }

    /**
     * Method for event 'newsletter_subscriber_save_after'.
     * The newsletter subscription is added or modified
     */
    public function newsletterSubscriptionModified(Varien_Event_Observer $observer)
    {
        // if the plug-in is not enabled, skip this
        if (!$this->enabled() || !$this->isValidStore()) return;

        // Do we have a valid item?
        if (!is_object($subscriber = $observer->getEvent()->getSubscriber())) return;

        // unfortunately hasDataChanges is only available in Mage 1.5 and up
        if (method_exists($subscriber, 'hasDataChanges') && !$subscriber->hasDataChanges()) {
            // an event is triggered every time the object is saved, even when nothing has changed
            // for example, when an order is placed
            return;
        }

        // get customer Id
        $customerId = $subscriber->getCustomerId();

        // This newsletter subscription should be added to the queue
        $queue = Mage::getModel('marketingsoftware/queue')
            ->setObject()
            ->setCustomer($customerId)
            ->setAction($subscriber->isObjectNew() ? 'add' : 'modify')
            ->setName('subscription')
            ->setEntityId($subscriber->getId())
            ->save();
    }

    /**
     * Method for event 'customer_delete_before'.
     * The customer is deleted, do something with it,
     */
    public function customerRemoved(Varien_Event_Observer $observer)
    {
        // if the plug-in is not enabled, skip this
        if (!$this->enabled() || !$this->isValidStore()) return;

        // Do we have a valid item?
        if (!is_object($customer = $observer->getEvent()->getCustomer())) return;

        // get customer Id
        $customerId = $customer->getId();

        // get copernica customer
        $copernicaCustomer = new Copernica_MarketingSoftware_Model_Copernica_Entity_Customer($customerId);

        // This customer should be added to the queue
        $queue = Mage::getModel('marketingsoftware/queue')
            ->setObject(array('profileId' => $copernicaCustomer->getProfileId(), 'email' => $customer->getEmail(), 'store_id' => $customer->getStoreId()))
            ->setCustomer($customerId)
            ->setAction('remove')
            ->setName('customer')
            ->setEntityId($customerId)
            ->save();
    }

    /**
     * Method for event 'customer_save_after'.
     * The customer is added or modified, do something with it,
     */
    public function customerModified(Varien_Event_Observer $observer)
    {
        // if the plug-in is not enabled, skip this
        if (!$this->enabled() || !$this->isValidStore()) return;

        // Do we have a valid item?
        if (!is_object($customer = $observer->getEvent()->getCustomer())) return;

        // get customer Id
        $customerId = $customer->getId();

        // just to be sure
        if (!$customerId) return;

        // This customer should be added to the queue
        $queue = Mage::getModel('marketingsoftware/queue')
            ->setObject(null)
            ->setCustomer($customerId)
            ->setAction($customer->isObjectNew() ? 'add' : 'modify')
            ->setName('customer')
            ->setEntityId($customerId)
            ->save();
    }

    /**
     * Method for event 'sales_quote_item_save_after'.
     * An item is added or modified
     */
    public function productViewed(Varien_Event_Observer $observer)
    {
        // if the plug-in is not enabled, skip this
        if (!$this->enabled() || !$this->isValidStore()) return;

        // Do we have a valid item?
        if (!is_object($item = $observer->getEvent()->getProduct())) return;

        // get current customer instance and Id
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        $customerId = $customer->getId();

        // this item cannot be linked to a customer, so is not relevant at this moment
        if (!$customerId) return;

        // This quote item should be added to the queue
        $queue = Mage::getModel('marketingsoftware/queue')
            ->setObject(array('customer' => $customerId))
            ->setCustomer($customerId)
            ->setAction('add')
            ->setName('view')
            ->setEntityId($item->getEntityId())
            ->save();
    }

    /**
     *  This function should run in fixed time period, and it's suppouse to detect
     *  all carts that were forgotten by clients
     */
    public function detectAbandonedCarts()
    {
        // check if extension is enabled
        if (!$this->enabled()) return;

        // create abandoned carts processor
        $processor = new Copernica_MarketingSoftware_Model_AbandonedCartsProcessor();

        // detect abandoned carts
        $processor->detectAbandonedCarts();
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
     *  This function will process current queue. Note that not whole queue can
     *  be processed in one run. User can specify the time and number of items
     *  to be processed in one run.
     */
    public function processQueue()
    {
        // if the plug-in is not enabled, skip this
        if (!$this->enabled() || !Mage::helper('marketingsoftware/config')->getVanillaCrons()) return;

        // create instance of queue processor
        $queueProcessor = Mage::getModel('Copernica_MarketingSoftware_Model_QueueProcessor');

        // process queue
        $queueProcessor->processQueue();
    }
}
