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
 * Queue object for accessing the events in the queue table.
 *
 */
class Copernica_MarketingSoftware_Model_Queue_Item extends Mage_Core_Model_Abstract
{
    /**
     *  Constructor for the model
     */
    protected function _construct()
    {
        $this->_init('marketingsoftware/queue_item');
    }

    /**
     *  Get 1st free queue event that is not locked by any lock.
     *  
     *  @return string
     */
    public function getFirstFree()
    {
        return $this->getResource()->getFirstFree();
    }

    /**
     *  Get the data from the model
     *
     *  @return mixed
     */
    public function getObject()
    {
        $data = $this->getData('object');

        $object = json_decode($data);

        if (!is_null($object)) {
            return $object;
        }

        return unserialize($this->getData('object'));
    }

    /**
     *  Set the data to the model
     *  
     *  @param    object  $object
     *  @return Copernica_MarketingSoftware_Model_Queue_Item
     */
    public function setObject($object = null)
    {
        if (is_null($object)) {
           $this->setData('object', '');

            return $this;
        }

        $json = json_encode($object);

        $this->setData('object', $json ? $json : '');

        return $this;
    }

    /**
     *  Set the customer that is interested in queue item
     *  
     *  @param  int    $customerId
     *  @return Copernica_MarketingSoftware_Model_Queue_Item
     */
    public function setCustomerId($customerId)
    {
        $this->setData('customer', $customerId);

        return $this;
    }

    /**
     *  Get customer that is
     *  
     *  @return id
     */
    public function getCustomerId()
    {
        return $this->getData('customer');
    }

    /**
     *  Set name of the event
     *  
     *  @param  string    $name
     *  @return Copernica_MarketingSoftware_Model_Queue_Item
     */
    public function setName($name)
    {
        $this->setData('name', $name);

        return $this;
    }

    /**
     *  Get name of the event
     *  
     *  @return string
     */
    public function getName()
    {
        return $this->getData('name');
    }

    /**
     *  Set associated entity Id
     *  
     *  @param  int    $id
     *  @return Copernica_MarketingSoftware_Model_Queue_Item
     */
    public function setEntityId($id)
    {
        $this->setData('entity_id', $id);

        return $this;
    }

    /**
     *  Get associate entity Id
     *  
     *  @return int
     */
    public function getEntityId()
    {
        return $this->getData('entity_id');
    }

    /**
     *  Process this item in the queue. Returns true if the event was
     *  successfully processed, otherwise returns false.
     *
     *  @return boolean
     */
    public function process()
    {
        $modelName = 'marketingsoftware/queue_event_'. $this->getName();

        if (!class_exists(Mage::getConfig()->getModelClassName($modelName))) {
            return null;
        }

        $event = Mage::getModel($modelName);
        $event->setQueueItem($this);
        
        return $event->process();
    }

    /**
     *  When saving queue event we should check if it should be saved. When on
     *  queue is already same event we don't have to save it again. This way
     *  we can save some CPU and network.
     *  Also if we are inserting 'remove' event there is no point in syncing all
     *  modify/add events before this 'remove' event. We can remove such event
     *  before we save one with 'remove'
     *
     *  @return Copernica_MarketingSoftware_Model_Queue_Item
     */
    public function save()
    {
        $this->setQueueTime(date("Y-m-d H:i:s"));

        if ($this->getAction() == 'remove') {
            $this->_clearNoRemoveSiblings();
        }

        if ($this->getAction() == 'add' || $this->getAction() == 'modify') {
            if ($this->_shouldSave()) {
                parent::save();
            } else {
                return $this;
            }
        }

        return parent::save();
    }

    /**
     *  Clear all modify/add events that are before this event.
     */
    protected function _clearNoRemoveSiblings()
    {
        $collection = $this->getCollection()
            ->addFilter('action', 'add')
            ->addFilter('name', $this->getName())
            ->addFilter('object', ($object = $this->getData('object')) ? $object : '')
            ->addFilter('entity_id', $this->getEntityId());

        foreach ($collection as $event) {
            $event->delete();
        }

        $collection = $this->getCollection()
            ->addFilter('action', 'modify')
            ->addFilter('name', $this->getName())
            ->addFilter('object', ($object = $this->getData('object')) ? $object : '')
            ->addFilter('entity_id', $this->getEntityId());

        foreach ($collection as $event) {
            $event->delete();
        }
    }

    /**
     *  Merge all siblings
     *  
     *  @return boolean
     */
    protected function _shouldSave()
    {
        $collection = $this->getCollection()
            ->addFilter('action', $this->getAction())
            ->addFilter('name', $this->getName())
            ->addFilter('object', ($object = $this->getData('object')) ? $object : '')
            ->addFilter('entity_id', $this->getEntityId());

        return $collection->count() == 0;
    }
}
