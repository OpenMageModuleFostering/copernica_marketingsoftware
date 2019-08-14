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
 *  This class will process task queue.
 */
class Copernica_MarketingSoftware_Model_Queue_Processor
{
    /**
     *  Number of processed tasks by this processor
     *  
     *  @var	int
     */
    protected $_processedTasks = 0;

    /**
     *  Timestamp when processor starts its job
     */
    protected $_startTime;

    /**
     *  How many items we want to process in one run?
     *  
     *  @var	int
     */
    protected $_itemsLimit = 10000000;

    /**
     *  For what is our timelimit for queue processing? in seconds.
     *  
     *  @var	int
     */
    protected $_timeLimit = 3075840000;

    /**
     *  Currently locked customer
     *  
     *  @var	int
     */
    protected $_currentCustomer = null;

    /**
     *  Construct object.
     *
     *  NOTE: This class is not a varien_object child! 
     */
    public function __construct()
    {
        $config = Mage::helper('marketingsoftware/config');

        if ($itemsLimit = $config->getItemsPerRun()) {
        	$this->_itemsLimit = $itemsLimit;
        }

        if ($timeLimit = $config->getTimePerRun()) {
        	$this->_timeLimit = $timeLimit;
        }
        
        $config = Mage::helper('marketingsoftware/config');
        $config->setLastStartTimeCronjob(date("Y-m-d H:i:s"));        
    }

    /**
     *  We want to make some final actions when this processor is beeing destroyed.
     */
    public function __destruct() 
    {
        $config = Mage::helper('marketingsoftware/config'); 
        $config->setLastEndTimeCronjob(date("Y-m-d H:i:s"));
        $config->setLastCronjobProcessedTasks($this->_processedTasks);
    }

    /**
     *  Try to aqcuire 1st lock that can be used to sync data.
     *  
     *  @return string
     */
    public function aqcuireLock()
    {
        $firstFree = Mage::getModel('marketingsoftware/queue_item')->getFirstFree();

        return $this->_currentCustomer = $firstFree;
    }

    /**
     *  Process queue with lock
     *  
     *  @param	string	$lock
     */
    public function processWithLocking($lock)
    {
        if (is_numeric($lock)) {
        	$this->processQueue($this->_currentCustomer);
        } elseif (is_null($lock)) {
        	$this->processQueue(null);
        }
    }

    /**
     *  Process queue
     *  
     *  @param	string	$customerId
     */
    public function processQueue($customerId = -1) 
    {   
        $maxExecutionTime = ini_get('max_execution_time');

        set_time_limit(0);

        $queue = Mage::getResourceModel('marketingsoftware/queue_item_collection')
            ->addDefaultOrder()->setPageSize($this->_itemsLimit < 150 ? 150 : $this->_itemsLimit);

        if (is_null($customerId)) {
        	$queue->addFieldToFilter('customer', array('null' => true));
        } elseif ($customerId > -1) {  
        	$queue->addFilter('customer', $customerId);
        }

        $this->_prepareProcessor();

        foreach ($queue as $item) {   
            if ($this->_isLimitsReached()) {
            	break;
            }

            $this->_processItem($item);
        }

        set_time_limit($maxExecutionTime);
    }

    /**
     *  Make some preparations before we start processing queue
     */
    protected function _prepareProcessor()
    {
        $this->_startTime = microtime(true);
    }

    /**
     *  Check if we reached limits
     *  
     *  @return bool
     */
    protected function _isLimitsReached() 
    {
        return $this->_processedTasks > $this->_itemsLimit || microtime(true) > $this->_startTime + $this->_timeLimit;
    }

    /**
     *  Process queue item
     *  
     *  @param	Copernica_MarketingSoftware_Model_Queue_Item	$item
     */
    protected function _processItem(Copernica_MarketingSoftware_Model_Queue_Item $item)
    {
        try {
            if ($item->process()) {
            	$item->delete();
            } else { 
            	$this->_transferItemToErrorQueue($item);
            }

            $this->_processedTasks++;
        } catch (Copernica_MarketingSoftware_Exception $copernicaException) {
            Mage::log($copernicaException->getMessage(), null, 'copernica_queue_exceptions.log');

            $this->_transferItemToErrorQueue($item);
        } catch (Exception $exception) {
            Mage::logException($exception);

            $item->setResult($exception->getMessage())->setResultTime(date('Y-m-d H:i:s'));
        }
    }

    /**
     *  Transfer queue item to error queue.
     *  
     *  @param	Copernica_MarketingSoftware_Model_Queue_Item	$item
     */
    protected function _transferItemToErrorQueue(Copernica_MarketingSoftware_Model_Queue_Item $item)
    {
        $errorItem = Copernica_MarketingSoftware_Model_Error_Queue::createFromQueueItem($item);
        $errorItem->save();

        $item->delete();
    }

    /**
     *  Fetch data about current run.
     *  
     *  @param	string	$type
     */
    public function fetchReport($type)
    {
        $data = array(
            'startTime' => date('Y-m-d H:i:s', (int)$this->_startTime),
            'runTime' => (microtime(true) - $this->_startTime),
            'processedTasks' => $this->_processedTasks,
            'itemsLimit' => $this->_itemsLimit,
            'timeLimit' => $this->_timeLimit,
            'lockedCustomer' => $this->_currentCustomer
        );

        $type = strtolower($type);

        switch ($type) {
            case 'json': 
                return json_encode($data);
                
            default:
                $report  = "    Started at ".$data['startTime'].' UTC'.PHP_EOL;
                $report .= "    Run took  ".$data['runTime']." seconds".PHP_EOL;
                $report .= "    Processed ".$data['processedTasks']." tasks".PHP_EOL;
                $report .= "    Limited with ".$data['itemsLimit']." items and ".$data['timeLimit']." seconds".PHP_EOL;
                $report .= "    ".($data['lockedCustomer'] ? "Locked on ".$data['lockedCustomer'] : "Without a lock").PHP_EOL;
                return $report;
        }
    } 
}