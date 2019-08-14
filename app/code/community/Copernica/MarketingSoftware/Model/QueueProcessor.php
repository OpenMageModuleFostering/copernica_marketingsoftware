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
 *  This class will process task queue.
 */
class Copernica_MarketingSoftware_Model_QueueProcessor
{
    /**
     *  Number of processed tasks by this processor
     *  @var int
     */
    private $processedTasks = 0;

    /**
     *  Timestamp when processor starts its job
     */
    private $startTime;

    /**
     *  How many items we want to process in one run?
     *  @var int
     */
    private $itemsLimit = 10000000;

    /**
     *  For what is our timelimit for queue processing? in seconds.
     *  @var int
     */
    private $timeLimit = 3075840000;

    /**
     *  Currently locked customer
     *  @var int
     */
    private $currentCustomer = null;

    /**
     *  Construct object.
     *
     *  NOTE: This class is not a varien_object child! 
     */
    public function __construct()
    {
        // get config into local scope
        $config = Mage::helper('marketingsoftware/config');

        // check if we should limit how many items we can process in one run
        if ($itemsLimit = $config->getItemsPerRun()) $this->itemsLimit = $itemsLimit;

        // check if we should limit how much time we should spend on processing
        if ($timeLimit = $config->getTimePerRun()) $this->timeLimit = $timeLimit;
    }

    /**
     *  We want to make some final actions when this processor is beeing destroyed.
     */
    public function __destruct() 
    {
        // get config into local scope
        $config = Mage::helper('marketingsoftware/config');

        // 
        $config->setLastStartTimeCronjob(date("Y-m-d H:i:s"));

        // set how many items we did process in last run
        $config->setLastCronjobProcessedTasks($this->processedTasks);
    }

    /**
     *  Try to aqcuire 1st lock that can be used to sync data.
     *  @return string
     */
    public function aqcuireLock()
    {
        $firstFree = Mage::getModel('marketingsoftware/queue')->getFirstFree();

        return $this->currentCustomer = $firstFree;
    }

    /**
     *  Process queue with lock
     *  @param string
     */
    public function processWithLocking($lock)
    {
        // process queue for one customer only
        if (is_numeric($lock)) $this->processQueue($this->currentCustomer);
        elseif (is_null($lock)) $this->processQueue(null);
    }

    /**
     *  Process queue
     *  @param string
     */
    public function processQueue($customerId = -1) 
    {   
        // what is the setting for max execution time?
        $maxExecutionTime = ini_get('max_execution_time');

        /*
         *  set unlimited time for script execution. It does not matter that much
         *  cause most time will be spent on database/curl calls and they do not 
         *  extend script execution time. We are setting this just to be sure that
         *  script will not terminate in the middle of processing.
         *  This is true for Linux machines. On windows machines it is super 
         *  important to set it to large value. Cause windows machines do use 
         *  real time to measure script execution time. When time limit is reached
         *  it will terminate connection and will not gracefully come back to 
         *  script execution. Such situation will just leave mess in database.
         */
        set_time_limit(0);

        // get queue items collection
        $queue = Mage::getResourceModel('marketingsoftware/queue_collection')
            ->addDefaultOrder()->setPageSize($this->itemsLimit < 150 ? 150 : $this->itemsLimit);

        // should we pick up all events without a customer?
        if (is_null($customerId)) $queue->addFieldToFilter('customer', array('null' => true));

        // shoulf we pick up all events that are binded to customer? 
        elseif ($customerId > -1) $queue->addFilter('customer', $customerId);

        // make some preparations before we start processing queue
        $this->prepareProcessor();

        // iterate over queue
        foreach ($queue as $item)
        {   
            // check if we did reach limit
            if ($this->isLimitsReached()) break;

            // check if did manage to process item
            $this->processItem($item);
        }

        /*
         *  Now, some explanation why we are doing such thing. 
         *  When we are processing tasks/events we are doing hell lot of 
         *  database/curl calls. They do not count into execution time cause cpu
         *  is not spending time on script (it's halted). This is why we can not 
         *  rely on php time counter and that is why we are making our own check.
         *  After we are done with processing, we will just reset time counter 
         *  for whole magento.
         *  Note that this is true for Linux systems. On windows based machines
         *  real time is used.
         */
        set_time_limit($maxExecutionTime);
    }

    /**
     *  Make some preparations before we start processing queue
     */
    private function prepareProcessor()
    {
        // store time when we start
        $this->startTime = microtime(true);
    }

    /**
     *  Check if we reached limits
     *  @return bool
     */
    private function isLimitsReached() 
    {
        // check if either we did reach maximum amount of items or we we processing too long
        return $this->processedTasks > $this->itemsLimit || microtime(true) > $this->startTime + $this->timeLimit;
    }

    /**
     *  Process queue item
     *  @param Copernica_MarketingSoftware_Queue
     */
    private function processItem($item)
    {
        try
        {
            // process item and remove it when everything went good
            if ($item->process()) $item->delete();

            // if we didn't process item correctly then transfer it to error queue
            else $this->transferItemToErrorQueue($item);

            // increment processed tasks counter
            $this->processedTasks++;
        }
        /*
         *  Copernica exceptions do mean that we have something wrong with API 
         *  configuration or we are missing something important. We want to handle
         *  them in more civilized way.
         */
        catch (Copernica_MarketingSoftware_Exception $copernicaException)
        {
            // log exception message to special log
            Mage::log($copernicaException->getMessage(), null, 'copernica_queue_exceptions.log');

            // we want to move problematic item to error queue
            $this->transferItemToErrorQueue($item);
        }
        /*
         *  When we have a non copernica exception it means that we have little
         *  controll over the reason why it happend. It could be numerous problems:
         *  network failure, hard disk turning in fireball, magento stinky code. 
         *  Basically we can not determine what to do with it. We can tell 
         *  magento to log the exception and we just run with the queue as 
         *  we do usual. 
         */
        catch (Exception $exception)
        {
            // tell magento to log exception
            Mage::logException($exception);

            // set result message on item and set result time
            $item->setResult($exception->getMessage())->setResultTime(date('Y-m-d H:i:s'));
        }
    }

    /**
     *  Transfer queue item to error queue.
     *  @param  Copernica_MarketingSoftware_Queue
     */
    private function transferItemToErrorQueue($item)
    {
        // create error queue item
        $errorItem = Copernica_MarketingSoftware_Model_ErrorQueue::createFromQueueItem($item);

        // save error item
        $errorItem->save();

        // remove item
        $item->delete();
    }

    /**
     *  Fetch data about current run.
     *  @var string
     */
    public function fetchReport($type)
    {
        $data = array(
            'startTime' => date('Y-m-d H:i:s', (int)$this->startTime),
            'runTime' => (microtime(true) - $this->startTime),
            'processedTasks' => $this->processedTasks,
            'itemsLimit' => $this->itemsLimit,
            'timeLimit' => $this->timeLimit,
            'lockedCustomer' => $this->currentCustomer
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