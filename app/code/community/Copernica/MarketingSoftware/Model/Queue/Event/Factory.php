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
 * Factory class for getting queue event objects
 */
class Copernica_MarketingSoftware_Model_Queue_Event_Factory
{
    /**
     *  Get the right object
     *
     *  @return Copernica_MarketingSoftware_Model_Abstraction_Quote|Copernica_MarketingSoftware_Model_Abstraction_Quote_item|Copernica_MarketingSoftware_Model_Abstraction_Customer|Copernica_MarketingSoftware_Model_Abstraction_Order|Copernica_MarketingSoftware_Model_Abstraction_Subscription|Copernica_MarketingSoftware_Model_Abstraction_Product_Viewed
     */
    public function get($queueItem)
    {
        if ($queueItem->getAction() == 'start_sync') {
            $classname = Mage::getConfig()->getModelClassName('marketingsoftware/queue_event_startsync');
            
            return new $classname($queueItem);
        }

        if ($queueItem->getAction() == 'upgrade') {
            $classname = Mage::getConfig()->getModelClassName('marketingsoftware/queue_event_customer_upgrade');
            
            return new $classname($queueItem);
        } 
    
        $action = ucfirst($queueItem->getAction());
        
        switch (get_class($queueItem->_getObject()))
        {
            case "Copernica_MarketingSoftware_Model_Abstraction_Quote":
                $classname = "marketingsoftware/queue_event_quote".$action;
                break;
    
            case "Copernica_MarketingSoftware_Model_Abstraction_Quote_item":
                $classname = "marketingsoftware/queue_event_quote_item".$action;
                break;
    
            case "Copernica_MarketingSoftware_Model_Abstraction_Customer":
                $classname = "marketingsoftware/queue_event_customer".$action;
                break;
    
            case "Copernica_MarketingSoftware_Model_Abstraction_Order":
                $classname = "marketingsoftware/queue_event_order".$action;
                break;
    
            case "Copernica_MarketingSoftware_Model_Abstraction_Subscription":
                $classname = "marketingsoftware/queue_event_subscription".$action;
                break;
                
            case "Copernica_MarketingSoftware_Model_Abstraction_Product_Viewed":
                $classname = "marketingsoftware/queue_event_product_viewed".$action;
                break;
                
            case "Copernica_MarketingSoftware_Model_Abstraction_Wishlist":
                $classname = "marketingsoftware/queue_event_wishlist".$action;
                break;                
        }

        if (!isset($classname)) {
            throw Mage::exception('Copernica_MarketingSoftware', 'Event type does not exists: '.$classname, Copernica_MarketingSoftware_Exception::EVENT_NO_TYPE);
        }
        
        $classname = Mage::getConfig()->getModelClassName($classname);
        
        if (!class_exists($classname)) {
            throw Mage::exception('Copernica_MarketingSoftware', 'Event type does not exists: '.$classname, Copernica_MarketingSoftware_Exception::EVENT_TYPE_NOT_EXISTS);
        }
        
        return new $classname($queueItem);
    }
}