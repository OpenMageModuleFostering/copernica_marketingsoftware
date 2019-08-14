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
 * Queue object for accessing the events in the queue table. 
 *
 */
class Copernica_MarketingSoftware_Model_Queue extends Mage_Core_Model_Abstract
{
    /**
     *  Constructor for the model
     */
    protected function _construct()
    {
        $this->_init('marketingsoftware/queue');
    }
    
    /**
     *  Get the data from the model
     *  
     *  @return mixed
     */
    public function getObject()
    {
        // return the value
        return unserialize(parent::getData('object'));
    }
    
    /**
     *  Set the data to the model
     *  
     *  @return mixed
     */
    public function setObject($object)
    {
        // set the value from the parent implementation
        return parent::setData('object', serialize($object));
    }
    
    /**
     *  Process this item in the queue. Returns true if the event was
     *  successfully processed, otherwise returns false.
     *  
     *  @return boolean
     */
    public function process()
    {
        // Get QueueEvent classname
        $factory = Mage::getSingleton('marketingsoftware/QueueEvent_Factory');
            	
    	// Wrap the event in a command object (pattern alarm!)
    	$event = $factory->get($this);  
    
        // call the process function on the object
        return $event->process();
    }
    
    /**
     *  Function to save the correct queue time
     *  
     *  @return Copernica_MarketingSoftware_Model_Queue
     */
    public function save()
    {
        // save the queuetime
        $this->setQueueTime(date("Y-m-d H:i:s"));
        
        // rely on parent
        return parent::save();
    }
}