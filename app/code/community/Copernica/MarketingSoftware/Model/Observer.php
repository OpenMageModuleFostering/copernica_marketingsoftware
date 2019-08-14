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
 * Observer object.
 */
class Copernica_MarketingSoftware_Model_Observer
{
    /**
     *  Check if event is added in store that we want to sync.
     *  
     *  @return bool
     */
    protected function _isValidStore()
    {
        $currentStoreId = Mage::app()->getStore()->getId();

        return Mage::helper('marketingsoftware/config')->isEnabledStore($currentStoreId);
    }

    /**
     * Method for the following events:
     * 'checkout_controller_onepage_save_shipping_method'
     * 'checkout_controller_multishipping_shipping_post'
     * This method is fired during checkout process, after the customer has entered billing address
     * and saved the shipping method
     * 
     * @param	Varien_Event_Observer	$observer
     */
    public function checkoutSaveStep(Varien_Event_Observer $observer)
    {
    	$quote = $observer->getEvent()->getQuote();
    	
        if (!$this->_enabled() || !$this->_isValidStore() || !is_object($quote)) {
        	return;
        }
        
        $customerId = $quote->getCustomerId(); 

        // !!!! Why is this called?? !!!!
        $order = $quote->getOrder();

        Mage::getModel('marketingsoftware/queue_item')
            ->setObject(array('customerId' => $customerId, 'quoteId' => $quote->getId()))
            ->setCustomer($customerId)
            ->setName('checkout')
            ->setAction('add')
            ->setEntityId($quote->getEntityId())
            ->save();
    }

    /**
     *  Method for event 'sales_quote_item_delete_before'.
     *  An item is removed from a quote
     *  
     *  @param	Varien_Event_Observer	$observer
     */
    public function quoteItemRemoved(Varien_Event_Observer $observer)
    {
    	$quoteItem = $observer->getEvent()->getItem();
    	
        if (!$this->_enabled() || !$this->_isValidStore() || !is_object($quoteItem) || $quoteItem->getParentItemId()) {
        	return;
        }

        $quote = $quoteItem->getQuote();

        $customerId = $quote->getCustomerId();
        
        if (!$customerId) {
        	if (!Mage::getSingleton('customer/session')->isLoggedIn()) {
        		return;
        	}
        
        	$customerId = Mage::getSingleton('customer/session')->getCustomer()->getId();
        
        	if (!$customerId) {
        		return;
        	}
        }

        $quoteItemEntity = Mage::getModel('marketingsoftware/copernica_entity_quote_item');
        $quoteItemEntity->setQuoteItem($quoteItem);
        
        $quoteItemData = array(
        	'item_id' => $quoteItemEntity->getId(),
        	'storeview_id' => $quoteItemEntity->getStoreView()->id(),
        	'status' => 'deleted',
        );

        $queue = Mage::getModel('marketingsoftware/queue_item')
            ->setObject(array('quoteId' => $quote->getId(), 'customerId' => $customerId, 'quoteItem' => $quoteItemData))
            ->setCustomer($customerId)            
            ->setName('item')
            ->setAction('remove')
            ->setEntityId($quoteItem->getId())
            ->save();
    }

    /**
     *  Method for event 'sales_quote_item_save_after'.
     *  An item is added or modified
     *  
     *  @param	Varien_Event_Observer	$observer
     */
    public function quoteItemModified(Varien_Event_Observer $observer)
    {
    	$quoteItem = $observer->getEvent()->getItem();
    	
        if (!$this->_enabled() || !$this->_isValidStore() || !is_object($quoteItem) || $quoteItem->getParentItemId()) {
        	return;
        }
        
        if (method_exists($quoteItem, 'hasDataChanges') && !$quoteItem->hasDataChanges()) {
            return;
        }
        
        $quote = $quoteItem->getQuote();
        
        $customerId = $quote->getCustomerId();

        if (!$customerId) {
            if (!Mage::getSingleton('customer/session')->isLoggedIn()) {
            	return;
            }

            $customerId = Mage::getSingleton('customer/session')->getCustomer()->getId();

            if (!$customerId) {
            	return;
            }
        }
        
        $queue = Mage::getModel('marketingsoftware/queue_item')
        	->setObject(array('quoteItemId' => $quoteItem->getId(), 'quoteId' => $quote->getId(), 'customerId' => $customerId))
        	->setCustomer($customerId)
        	->setName('item')
        	->setAction($quoteItem->isObjectNew() ? 'add' : 'modify')
        	->setEntityId($quoteItem->getId())
        	->save();       
    }

    /**
     *  Listen to when quote is removed.
     *  
     *  @todo	Review this one
     *  @param	Varien_Event_Observer	$observer
     */
    public function quoteDelete(Varien_Event_Observer $observer)
    {
    	$quote = $observer->getEvent()->getQuote();
    	
        if (!$this->_enabled() || !$this->_isValidStore() || !is_object($quote)) {
        	return;
        }

        $customerId = $quote->getCustomerId();

        if (!$customerId) {
            if (!Mage::getSingleton('customer/session')->isLoggedIn()) {
            	return;
            }

            $customerId = Mage::getSingleton('customer/session')->getCustomer()->getId();

            if (!$customerId) {
            	return;
            }
        }

        $email = $quote->getCustomerEmail();
        $email = $email ? $email : $quote->getBillingAddress()->getEmail();
        $email = $email ? $email : $quote->getShippingAddress()->getEmail();

        $store = $quote->getStore();
        $website = $store->getWebsite();
        $group = $store->getGroup();

        $storeView = implode(' > ', array ($website->getName(), $group->getName(), $store->getName()));

        if ($email) {
        	Mage::getModel('marketingsoftware/queue_item')
        		->setObject(array('storeView' => $storeView, 'quoteId' => $quote->getId(), 'customerId' => $customerId))
            	->setCustomer($customerId)
            	->setName('quote')
            	->setAction('remove')            
            	->setEntityId($quote->getEntityId())
            	->save();
        }
    }

    /**
     * Method for event 'sales_order_save_after'.
     * An order is added or modified
     */
    public function orderModified(Varien_Event_Observer $observer)
    {
    	$order = $observer->getEvent()->getOrder();
    	
        if (!$this->_enabled() || !$this->_isValidStore() || !is_object($order) || !$order->getState()) {
        	return;
        }

        $customerId = $order->getCustomerId();

        $queue = Mage::getModel('marketingsoftware/queue_item')
            ->setObject(array('customerId' => $customerId, 'orderId' => $order->getId()))
            ->setCustomer($customerId)
            ->setName('order')
            ->setAction($order->isObjectNew() ? 'add' : 'modify')            
            ->setEntityId($order->getEntityId())
            ->save();
    }

    /**
     * Method for event 'wishlist_save_after'.
     * An wishlist item is added or modified
     */
    public function wishlistItemModified(Varien_Event_Observer $observer)
    {
	    $wishlistItem = $observer->getEvent()->getItem();	    	    
	    
        if (!$this->_enabled() || !$this->_isValidStore() || !is_object($wishlistItem)) {
        	return;
        }               
        
        $wishlist = Mage::getModel('wishlist/wishlist')->load($wishlistItem->getWishlistId());
            
        $customerId = $wishlist->getCustomerId();
        
        $queue = Mage::getModel('marketingsoftware/queue_item')
        	->setObject(array('wishlistItemId' => $wishlistItem->getId(), 'customerId' => $customerId))
        	->setCustomer($customerId)
        	->setName('wishlist_item')
        	->setAction($wishlistItem->isObjectNew() ? 'add' : 'modify')
        	->setEntityId($wishlistItem->getId())
        	->save();  
    }
    
    /**
     * Method for event 'newsletter_subscriber_delete_before'.
     * The newsletter subscription is deleted, do something with it,
     */
    public function newsletterSubscriptionRemoved(Varien_Event_Observer $observer)
    {
    	$subscriber = $observer->getEvent()->getSubscriber();
    	
        if (!$this->_enabled() || !$this->_isValidStore() || !is_object($subscriber)) {
        	return;
        }

        $customerId = $subscriber->getCustomerId();

        $queue = Mage::getModel('marketingsoftware/queue_item')
            ->setObject(array('storeId' => $subscriber->getStoreId(), 'email' => $subscriber->getSubscriberEmail()))
            ->setCustomer($customerId ? $customerId : null)
            ->setName('subscription')
            ->setAction('remove')            
            ->setEntityId($subscriber->getId())
            ->save();
    }

    /**
     * Method for event 'newsletter_subscriber_save_after'.
     * The newsletter subscription is added or modified
     */
    public function newsletterSubscriptionModified(Varien_Event_Observer $observer)
    {
    	$subscriber = $observer->getEvent()->getSubscriber();
    	
        if (!$this->_enabled() || !$this->_isValidStore() || !is_object($subscriber)) {
        	return;
        }
        
        if (method_exists($subscriber, 'hasDataChanges') && !$subscriber->hasDataChanges()) {
            return;
        }
        $customerId = $subscriber->getCustomerId();

        $queue = Mage::getModel('marketingsoftware/queue_item')
            ->setObject(array('subscriberId' => $subscriber->getId()))
            ->setCustomer($customerId)
            ->setName('subscription')
            ->setAction($subscriber->isObjectNew() ? 'add' : 'modify')
            ->setEntityId($subscriber->getId())
            ->save();
    }

    /**
     * Method for event 'customer_delete_before'.
     * The customer is deleted, do something with it,
     */
    public function customerRemoved(Varien_Event_Observer $observer)
    {
    	$customer = $observer->getEvent()->getCustomer();
    	
        if (!$this->_enabled() || !$this->_isValidStore() || !is_object($customer)) {
        	return;
        }

        $customerId = $customer->getId();
        
        if (!$customerId) {
        	if (!Mage::getSingleton('customer/session')->isLoggedIn()) {
        		return;
        	}
        
        	$customerId = Mage::getSingleton('customer/session')->getCustomer()->getId();
        
        	if (!$customerId) {
        		return;
        	}
        }
        
        $customerEntity = Mage::getModel('marketingsoftware/copernica_entity_customer');
        $customerEntity->setCustomer($customerId);

        $queue = Mage::getModel('marketingsoftware/queue_item')
            ->setObject(array('profileId' => $customerEntity->getProfileId(), 'email' => $customer->getEmail(), 'storeId' => $customer->getStoreId()))
            ->setCustomer($customerId)
            ->setAction('remove')
            ->setName('customer')
            ->setEntityId($customer->getEntityId())
            ->save();
    }

    /**
     * Method for event 'customer_save_after'.
     * The customer is added or modified, do something with it,
     */
    public function customerModified(Varien_Event_Observer $observer)
    {
    	$address = $observer->getEvent()->getCustomerAddress();
    	
    	if(is_object($address)) {
    		$customer = $address->getCustomer();
    	} else {
    		$customer = $observer->getEvent()->getCustomer();
    	}
    	    	    
        if (!$this->_enabled() || !$this->_isValidStore() || !is_object($customer)) {
        	return;
        }

        $customerId = $customer->getId();
        
		if (!$customerId) {
        	if (!Mage::getSingleton('customer/session')->isLoggedIn()) {
        		return;
        	}
        
        	$customerId = Mage::getSingleton('customer/session')->getCustomer()->getId();
        
        	if (!$customerId) {
        		return;
        	}
        }

        $queue = Mage::getModel('marketingsoftware/queue_item')
            ->setObject(array('customerId' => $customerId))
            ->setCustomer($customerId)
            ->setAction($customer->isObjectNew() ? 'add' : 'modify')
            ->setName('customer')
            ->setEntityId($customer->getEntityId())
            ->save();
    }

    /**
     * Method for event 'sales_quote_item_save_after'.
     * An item is added or modified
     */
    public function productViewed(Varien_Event_Observer $observer)
    {
    	$product = $observer->getEvent()->getProduct();
    	
        if (!$this->_enabled() || !$this->_isValidStore() || !is_object($product)) {
        	return;
        }

        $customer = Mage::getSingleton('customer/session')->getCustomer();
        
        $customerId = $customer->getId();
        
        if (!$customerId) {
        	if (!Mage::getSingleton('customer/session')->isLoggedIn()) {
        		return;
        	}
        
        	$customerId = Mage::getSingleton('customer/session')->getCustomer()->getId();
        
        	if (!$customerId) {
        		return;
        	}
        }
        
        $queue = Mage::getModel('marketingsoftware/queue_item')
            ->setObject(array('storeId' => Mage::app()->getStore()->getId(), 'customerId' => $customerId, 'productId' => $product->getId(), 'viewedAt' => (string) time()))
            ->setCustomer($customerId)
            ->setAction('add')
            ->setName('view')
            ->setEntityId($product->getEntityId())
            ->save();
    }

    /**
     *  This function should run in fixed time period, and it's suppouse to detect
     *  all carts that were forgotten by clients
     */
    public function detectAbandonedCarts()
    {
        if (!$this->_enabled()) {
        	return;
        }

        $processor = Mage::getModel('marketingsoftware/abandoned_carts_processor');
        $processor->detectAbandonedCarts();
    }

    /**
     * Is the Copernica module enabled?
     *
     * @return boolean
     */
    protected function _enabled()
    {
        return Mage::helper('marketingsoftware')->enabled();
    }

    /**
     *  This function will process current queue. Note that not whole queue can
     *  be processed in one run. User can specify the time and number of items
     *  to be processed in one run.
     */
    public function processQueue()
    {
        if (!$this->_enabled() || !Mage::helper('marketingsoftware/config')->getVanillaCrons()) {
        	return;
        }

        $queueProcessor = Mage::getModel('marketingsoftware/queue_processor');
        $queueProcessor->processQueue();
    }
}
