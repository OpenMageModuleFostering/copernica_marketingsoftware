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
abstract class Copernica_MarketingSoftware_Model_QueueEvent_Abstract
{
    /**
     *  What queue item was used to construct this item
     *  @var Copernica_MarketingSoftware_Model_Queue
     */
    protected $queueItem;

    /**
     *  Construct the item given the queueitem
     *  
     *  @param Copernica_MarketingSoftware_Model_Queue $queueItem
     */
    protected function __construct($queueItem)
    {
        $this->queueItem = $queueItem;
    }

    /**
     *  We will use this factory method to create proper event.
     *  @param  Copernica_MarketingSoftware_Model_Queue
     *  @return Copernica_MarketingSoftware_Model_QueueEvent_Abstract
     */
    static public function create($queueItem)
    {
        // get classname of event that we want to create
        $classname = 'Copernica_MarketingSoftware_Model_QueueEvent_'.ucfirst($queueItem->getName());

        // check if desired class exists
        if (!class_exists($classname)) return null;

        // create new queue event and return it
        return new $classname($queueItem);
    }

    /**
     *  Get the object for this queue item
     *  @return Abstraction object
     */
    protected function getObject()
    {
        return $this->queueItem->getObject();
    }

    /**
     *  Get entity Id
     */
    protected function getEntityId()
    {
        return $this->queueItem->getEntityId();
    }

    /**
     *  Get customer Id
     *  @return int
     */
    protected function getCustomerId()
    {
        return $this->queueItem->getCustomerId();
    }

    /**
     *  Process this item in the queue
     *  @return boolean was the processing successfull
     */
    public function process()
    {
        // desired method that we want to call
        $methodName = 'action'.ucfirst($this->queueItem->getAction());

        // check if desired method exists
        if (!method_exists($this, $methodName)) return false;

        // run desired method
        return $this->$methodName();
    }

    /**
     *  Respawn event on the queue.
     */
    public function respawn() 
    {
        // create new item on event queue
        Mage::getModel('marketingsoftware/queue')
            ->setObject($this->queueItem->getObject())
            ->setCustomer($this->queueItem->getCustomer())
            ->setAction($this->queueItem->getAction())
            ->save();
    }
}