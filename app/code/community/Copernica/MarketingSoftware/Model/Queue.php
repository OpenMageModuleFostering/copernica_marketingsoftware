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
     *  Get 1st free queue event that is not locked by any lock.
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
        // get raw data store in row
        $data = parent::getData('object');

        // try to decode object
        $object = json_decode($data);

        /*
         *  Old format was storing serialized objects data in this field, thus
         *  there is a possibility that some of that old data will be still
         *  present in table data and json_decode will return null value. In
         *  such case we will fallback on default unserialize function.
         */
        if (!is_null($object)) return $object;

        // unserialize data
        return unserialize(parent::getData('object'));
    }

    /**
     *  Set the data to the model
     *  @param  object  Object that will be serialized and stored with queue item
     *  @return self
     */
    public function setObject($object = null)
    {
        // should we reset the data object
        if (is_null($object)) 
        {
            // set data object to empty string
            parent::setData('object', '');

            // allow chaining
            return $this;
        }

        // encode object
        $json = json_encode($object);

        // set the value from the parent implementation
        parent::setData('object', $json ? $json : '');

        // allow chaining
        return $this;
    }

    /**
     *  Set the customer that is interested in queue item
     *  @param  int
     *  @return self
     */
    public function setCustomerId($customerId)
    {
        parent::setData('customer', $customerId);

        // allow chaining
        return $this;
    }

    /**
     *  Get customer that is
     *  @return id
     */
    public function getCustomerId()
    {
        // get stored customer Id
        return parent::getData('customer');
    }

    /**
     *  Set name of the event
     *  @param  string
     *  @return self
     */
    public function setName($name)
    {
        parent::setData('name', $name);

        // allow chaining
        return $this;
    }

    /**
     *  Get name of the event
     *  @return string
     */
    public function getName()
    {
        return parent::getData('name');
    }

    /**
     *  Set associated entity Id
     *  @param  int
     *  @return self
     */
    public function setEntityId($id)
    {
        parent::setData('entity_id', $id);

        // allow chaining
        return $this;
    }

    /**
     *  Get associate entity Id
     *  @return int
     */
    public function getEntityId()
    {
        return parent::getData('entity_id');
    }

    /**
     *  Process this item in the queue. Returns true if the event was
     *  successfully processed, otherwise returns false.
     *
     *  @return boolean
     */
    public function process()
    {
        $event = Copernica_MarketingSoftware_Model_QueueEvent_Abstract::create($this);

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
     *  @return Copernica_MarketingSoftware_Model_Queue
     */
    public function save()
    {
        // save the queuetime
        $this->setQueueTime(date("Y-m-d H:i:s"));

        // check if we should remove non remove siblings
        if ($this->getAction() == 'remove') $this->clearNoRemoveSiblings();

        // we want to merge syblings only for 'add' and 'modify' events
        if ($this->getAction() == 'add' || $this->getAction() == 'modify')
        {
            if ($this->shouldSave()) parent::save();
            else return $this;
        }

        // rely on parent
        return parent::save();
    }

    /**
     *  Clear all modify/add events that are before this event.
     */
    private function clearNoRemoveSiblings()
    {
        // try to get a collection with elements that are the same as current one but are 'add' actions
        $collection = $this->getCollection()
            ->addFilter('action', 'add')
            ->addFilter('name', $this->getName())
            ->addFilter('object', ($object = parent::getData('object')) ? $object : '')
            ->addFilter('entity_id', $this->getEntityId());

        // remove all add or modifi actions
        foreach ($collection as $event) $event->delete();

        // try to get a collection with elements that are the same as current one but are 'modify' actions
        $collection = $this->getCollection()
            ->addFilter('action', 'modify')
            ->addFilter('name', $this->getName())
            ->addFilter('object', ($object = parent::getData('object')) ? $object : '')
            ->addFilter('entity_id', $this->getEntityId());

        // remove all add or modifi actions
        foreach ($collection as $event) $event->delete();
    }

    /**
     *  Merge all siblings
     *  @return boolean
     */
    private function shouldSave()
    {
        // try to get a collection with elements that are the same as current one
        $collection = $this->getCollection()
            ->addFilter('action', $this->getAction())
            ->addFilter('name', $this->getName())
            ->addFilter('object', ($object = parent::getData('object')) ? $object : '')
            ->addFilter('entity_id', $this->getEntityId());

        // if there are the same element we should not save
        return $collection->count() == 0;
    }
}
