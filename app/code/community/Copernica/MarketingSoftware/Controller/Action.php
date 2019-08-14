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
 *  This class will be a base for our visual constrollers.
 */
class Copernica_MarketingSoftware_Controller_Action extends Mage_Adminhtml_Controller_Action
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
    protected $_notes = array();

    /**
     *  index action.
     */
    public function _construct()
    {
        $queue = Mage::getModel('marketingsoftware/queue_item')->getCollection();
        
        $this->_checkQueueSize($queue);

        $this->_checkQueueTime($queue);

        $this->_checkApiAccess();
    }

    /**
     *  Check if queue have too many items. This method will output messages on 
     *  admin session to inform him about current state.
     *  
     *  @param  Copernica_MarketingSoftware_Model_Mysql4_Queue_Item_Collection	$queue
     */
    protected function _checkQueueSize(Copernica_MarketingSoftware_Model_Mysql4_Queue_Item_Collection $queue)
    {
        $queueSize = $queue->getSize();

        if ($queueSize < 100) {
        	return;
        }

        $this->_addWarning(Mage::helper('marketingsoftware')->__("There is queue of %d local modification waiting to be processed", $queueSize));
    }

    /**
     *  Check if queue have too old items.
     *  
     *  @param  Copernica_MarketingSoftware_Model_Mysql4_Queue_Item_Collection	$queue
     */
    protected function _checkQueueTime(Copernica_MarketingSoftware_Model_Mysql4_Queue_Item_Collection $queue)
    {
        $oldestItemTimestamp = $queue->getQueueStartTime();

        if (is_null($oldestItemTimestamp)) {
        	return;
        }

        if (time() - strtotime($oldestItemTimestamp) < 60*60*24) {
        	return;
        }

        $printableTime = Mage::helper('core')->formatDate($oldestItemTimestamp, 'short', true);

        $this->_addWarning(Mage::helper('marketingsoftware')->__("There is still a modification of %s that is not synchronized with Copernica.", $printableTime));
    }

    /**
     *  This function will check if we have an access to API.
     */
    protected function _checkApiAccess()
    {
        $accessToken = Mage::helper('marketingsoftware/config')->getAccessToken();

        if ($accessToken) {
        	return;
        }

        $this->_addWarning(Mage::helper('marketingsoftware')->__('There is no access token for API communication.'));
    }

    /**
     *  Add warning message to controller.
     *  
     *  @param  string	$text
     *  @return self
     */
    protected function _addWarning($text) 
    {
        $this->_notes['warnings'][] = $text;
        
        return $this;
    }

    /**
     *  Add error message to controller.
     *  
     *  @todo	Not used???
     *  @param  string	$text
     *  @return self
     */
    protected function _addError($text) 
    {
        $this->_notes['errors'][] = $text;
        
        return $this;
    }

    /**
     *  Get current list of warnings.
     *  
     *  @return array
     */
    public function getWarnings()
    {
        return $this->_getNotifications('warnings');
    }

    /**
     *  Get current list of errors
     *  
     *  @return array
     */
    public function getErrors()
    {
        return $this->_getNotifications('errors');
    }

    /**
     *  Get notifications of given type
     *  
     *  @param  string	$type
     *  @return array
     */
    protected function _getNotifications($type) 
    {
        if (!array_key_exists($type, $this->_notes)) {
        	return array();
        }

        $output = $this->_notes[$type];

        if(is_array($output)) {
        	return $output;
        }

        return array();
    }
}
