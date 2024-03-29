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
 *  This class will represent item from error queue.
 */
class Copernica_MarketingSoftware_Model_Error_Queue extends Mage_Core_Model_Abstract
{
    /**
     *  Construct the model
     */
    protected function _construct()
    {
        $this->_init('marketingsoftware/error_queue');
    }

    /**
     *  Get object instance. 
     *  
     *  @return object
     */
    public function getObject()
    {
        return unserialize(parent::getData('object'));
    }

    /**
     *  Set object instance
     *  
     *  @param    object    $object
     */
    public function setObject($object)
    {
        return parent::setData('object', serialize($object));
    }

    /**
     *  Set customer Id
     *  
     *  @param    int    $customerId
     */
    public function setCustomerId($customerId)
    {
        return parent::setData('customer', $customerId);
    }

    /**
     *  Get customer Id
     *  
     *  @return int
     */
    public function getCustomerId()
    {
        return parent::getData('customer');
    }

    /**
     *  Save model
     */
    public function save()
    {
        return parent::save();
    }

    /**
     *  This method will create instance from processing queue item
     *
     * @param Copernica_MarketingSoftware_Model_Queue_Item $queueItem
     */
    static public function createFromQueueItem(Copernica_MarketingSoftware_Model_Queue_Item $queueItem)
    {
        $errorModel = Mage::getModel('marketingsoftware/error_queue');
        $errorModel->setObject($queueItem->getObject());
        $errorModel->setOldId($queueItem->getId());
        $errorModel->setAction($queueItem->getAction());
        $errorModel->setQueueTime($queueItem->getQueueTime());
        $errorModel->setResult($queueItem->getResult());
        $errorModel->setResultTime($queueItem->getResultTime());
        $errorModel->setCustomerId($queueItem->getCustomerId());

        return $errorModel;
    }
}