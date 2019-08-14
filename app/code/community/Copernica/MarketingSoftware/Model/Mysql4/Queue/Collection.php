<?php
class Copernica_MarketingSoftware_Model_Mysql4_Queue_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('marketingsoftware/queue');
    }
   
    /**
     *  Add a default order, sorted ascending by queue time
     *  @return Copernica_MarketingSoftware_Model_Mysql4_Queue_Collection
     */
    public function addDefaultOrder()
    {
        // If a result was processed before, we should process it if we have nothing 
        // else to process, we want to import the queue items without an result_time 
        // first and then in order of queue time.
        return $this->addOrder('result_time', self::SORT_ORDER_ASC)
                ->addOrder('queue_time', self::SORT_ORDER_ASC);
    }
    
    /**
     *  Get the time of the oldest record
     *  @return string  mysql formatted date timestamp
     */
    public function getQueueStartTime()
    {
        return $this->addDefaultOrder()->setPageSize(1)->getFirstItem()->getQueueTime();
    }
    
    /**
     *  Get the result of the oldest record
     *  @return string  message
     */
    public function getOldestResult()
    {
        return $this->addDefaultOrder()->setPageSize(1)->getFirstItem()->getResult();
    }
}