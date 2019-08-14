<?php
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
     *  @return mixed
     */
    public function getObject()
    {
        // return the value
        return unserialize(parent::getData('object'));
    }
    
    /**
     *  Set the data to the model
     *  @return mixed
     */
    public function setObject($object)
    {
        // set the value from the parent implementation
        return parent::setData('object', serialize($object));
    }
    
    /**
     *  Process this item in the queue
     *  @return boolean was the processing successfull
     */
    public function process()
    {
        // Wrap the event in a command object (pattern alarm!)
        $event = Copernica_MarketingSoftware_Model_QueueEvent_Abstract::get($this);
    
        // call the process function on the object
        return $event->process();
    }
    
    /**
     *  Function to save the correct queue time
     */
    public function save()
    {
        // save the queuetime
        $this->setQueueTime(date("Y-m-d H:i:s"));
        
        // rely on parent
        return parent::save();
    }
}