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
 *  This class will be a base for our visual constrollers.
 */
class Copernica_MarketingSoftware_Controller_Base extends Mage_Adminhtml_Controller_Action
{
    /**
     *  Since default magento notification system is being derpy from version to 
     *  version we will use our own one. All notes that we will want to display
     *  will be stored in this arrays as entries. For now we will just store it 
     *  in super easy format. 
     *  
     *  array (
     *      'errors' => string[],
     *      'warnings' => string[]
     *  )
     *  
     *  @var    array
     */
    private $notes = array();

    /**
     *  index action.
     */
    public function _construct()
    {
        // get queue
        $queue = Mage::getResourceModel('marketingsoftware/queue_collection');

        // check current queue state
        $this->checkQueueSize($queue);

        // check current queue time
        $this->checkQueueTime($queue);

        // we want to check api access
        $this->checkApiAccess();
    }

    /**
     *  Check if queue have too many items. This method will output messages on 
     *  admin session to inform him about current state.
     *  @param  Copernica_MarketingSoftware_Model_Mysql4_Queue_Collection
     */
    private function checkQueueSize($queue)
    {
        // get queue size
        $queueSize = $queue->getSize();

        // we will not panic if queue size is below 100
        if ($queueSize < 100) return;

        // we will use our own notification system
        $this->addWarning(Mage::helper('marketingsoftware')->__("There is queue of %d local modification waiting to be processed", $queueSize));
    }

    /**
     *  Check if queue have too old items.
     *  @param  Copernica_MarketingSoftware_Model_Mysql4_Queue_Collection
     */
    private function checkQueueTime($queue)
    {
        // get queue oldest item timestamp
        $oldestItemTimestamp = $queue->getQueueStartTime();

        // check if there is an oldest timestamp
        if (is_null($oldestItemTimestamp)) return;

        // if oldest item age is less than 24 hours we will not panic
        if (time() - strtotime($oldestItemTimestamp) < 60*60*24) return;

        // we want to get a printable time
        $printableTime = Mage::helper('core')->formatDate($oldestItemTimestamp, 'short', true);

        // add warning to controller
        $this->addWarning(Mage::helper('marketingsoftware')->__("There is still a modification of %s that is not synchronized with Copernica.", $printableTime));
    }

    /**
     *  This function will check if we have an access to API.
     */
    private function checkApiAccess()
    {
        // try to get access token from config file
        $accessToken = Mage::helper('marketingsoftware/config')->getAccessToken();

        // if we have someting in access token we will assume that it's correct
        if ($accessToken) return;

        // add warning to controller
        $this->addWarning(Mage::helper('marketingsoftware')->__('There is no access token for API communication.'));
    }

    /**
     *  Add warning message to controller.
     *  @param  string
     *  @return self
     */
    protected function addWarning($text) 
    {
        $this->notes['warnings'][] = $text;
        return $this;
    }

    /**
     *  Add error message to controller.
     *  @param  string
     *  @return self
     */
    protected function addError($text) 
    {
        $this->notes['errors'][] = $text;
        return $this;
    }

    /**
     *  Get current list of warnings.
     *  @return array
     */
    public function getWarnings()
    {
        return $this->getNotifications('warnings');
    }

    /**
     *  Get current list of errors
     *  @return array
     */
    public function getErrors()
    {
        return $this->getNotifications('errors');
    }

    /**
     *  Get notifications of given type
     *  @param  string
     *  @return array
     */
    private function getNotifications($type) 
    {
        // check if we did set some notes of given type
        if (!array_key_exists($type, $this->notes)) return array();

        // get notes of given type array
        $output = $this->notes[$type];

        // ensure that output is an array
        if(is_array($output)) return $output;

        // return empty array
        return array();
    }
}