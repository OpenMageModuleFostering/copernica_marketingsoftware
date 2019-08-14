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
 *  A wrapper object around an event
 */
abstract class Copernica_MarketingSoftware_Model_Queue_Event_Abstract
{
    /**
     *  What queue item was used to construct this item
     *  
     *  @var    Copernica_MarketingSoftware_Model_Queue_Item
     */
    protected $_queueItem;

    /**
     *  Construct the item given the queueitem
     *  
     *  @param    Copernica_MarketingSoftware_Model_Queue_Item    $item
     */
    public function setQueueItem(Copernica_MarketingSoftware_Model_Queue_Item $item)
    {
        $this->_queueItem = $item;
    }

    /**
     *  Get the object for this queue item
     *  
     *  @todo    Not used?
     *  @return    Abstraction object
     */
    protected function _getObject()
    {
        return $this->_queueItem->getObject();
    }

    /**
     *  Get the entity Id
     *  
     *  @todo    Not used?
     *  @return    int
     */
    protected function _getEntityId()
    {
        return $this->_queueItem->getEntityId();
    }

    /**
     *  Get the customer Id
     *  
     *  @todo    Not used?
     *  @return int
     */
    protected function _getCustomerId()
    {
        return $this->_queueItem->getCustomerId();
    }

    /**
     *  Process this item in the queue
     *  
     *  @return boolean was the processing successfull
     */
    public function process()
    {
        $methodName = 'action'.ucfirst($this->_queueItem->getAction());

        if (!method_exists($this, $methodName)) {
            return false;
        }

        return $this->$methodName();
    }

    /**
     *  Respawn event on the queue.
     */
    public function respawn() 
    {
        Mage::getModel('marketingsoftware/queue_item')
            ->setObject($this->_queueItem->getObject())
            ->setCustomer($this->_queueItem->getCustomer())
            ->setAction($this->_queueItem->getAction())
            ->save();
    }
}