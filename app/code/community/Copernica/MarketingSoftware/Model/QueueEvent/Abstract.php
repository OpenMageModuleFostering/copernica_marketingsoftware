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

require_once(dirname(__FILE__).'/../Error.php');
/**
 *  A wrapper object around an event
 */
abstract class Copernica_MarketingSoftware_Model_QueueEvent_Abstract
{
    /**
     *  What queue item was used to construct this item
     *  @var Copernica_MarketingSoftware_Model_Queue
     */
    protected $queueItem;

    /**
     *  Construct the item given the queueitem
     *  
     *  @param Copernica_MarketingSoftware_Model_Queue $queueItem
     */
    public function __construct($queueItem)
    {
        $this->queueItem = $queueItem;
    }

    /**
     *  Get the object for this queue item
     *  @return Abstraction object
     */
    protected function getObject()
    {
        return $this->queueItem->getObject();
    }

    /**
     *  Process this item in the queue
     *  @return boolean was the processing successfull
     */
    public abstract function process();
}