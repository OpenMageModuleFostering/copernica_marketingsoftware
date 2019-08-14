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
 * Factory class for getting queue event objects
 */
class Copernica_MarketingSoftware_Model_QueueEvent_Factory
{
    /**
     *  Get the right object
     *
     *  @return Copernica_MarketingSoftware_Model_QueueEvent_Abstract
     */
    public function get($queueItem)
    {
        // If we want to start a full synchronisation, we should return a start sync object
        if ($queueItem->getAction() == 'start_sync') 
        {
            $classname = Mage::getConfig()->getModelClassName('marketingsoftware/QueueEvent_StartSync');
            return new $classname($queueItem);
        }

        // if we want to upgrade customer, we should return proper event
        if ($queueItem->getAction() == 'upgrade') 
        {
            $classname = Mage::getConfig()->getModelClassName('marketingsoftware/QueueEvent_CustomerUpgrade');
            return new $classname($queueItem);
        } 
    
        // Prepare the action, to append it to the classname
        $action = ucfirst($queueItem->getAction());
        
        // What kind of class is given
        switch (get_class($queueItem->getObject()))
        {
            case "Copernica_MarketingSoftware_Model_Abstraction_Quote":
                $classname = "marketingsoftware/QueueEvent_Quote".$action;
                break;
    
            case "Copernica_MarketingSoftware_Model_Abstraction_Quote_Item":
                $classname = "marketingsoftware/QueueEvent_QuoteItem".$action;
                break;
    
            case "Copernica_MarketingSoftware_Model_Abstraction_Customer":
                $classname = "marketingsoftware/QueueEvent_Customer".$action;
                break;
    
            case "Copernica_MarketingSoftware_Model_Abstraction_Order":
                $classname = "marketingsoftware/QueueEvent_Order".$action;
                break;
    
            case "Copernica_MarketingSoftware_Model_Abstraction_Subscription":
                $classname = "marketingsoftware/QueueEvent_Subscription".$action;
                break;
                
            case "Copernica_MarketingSoftware_Model_Abstraction_Viewedproduct":
                $classname = "marketingsoftware/QueueEvent_ViewedProduct".$action;
                break;
        }

        if (!isset($classname))
            throw Mage::exception('Copernica_MarketingSoftware', 'Event type does not exists: '.$classname, Copernica_MarketingSoftware_Exception::EVENT_NO_TYPE);

        // Get correct classname
        $classname = Mage::getConfig()->getModelClassName($classname);
        if (!class_exists($classname)) 
            throw Mage::exception('Copernica_MarketingSoftware', 'Event type does not exists: '.$classname, Copernica_MarketingSoftware_Exception::EVENT_TYPE_NOT_EXISTS);

        // construct the object
        return new $classname($queueItem);
    }
}