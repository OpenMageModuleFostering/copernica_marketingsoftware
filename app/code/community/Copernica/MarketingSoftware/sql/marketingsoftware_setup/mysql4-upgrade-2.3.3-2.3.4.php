<?php
/**
* Copernica Marketing Software
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to copernica@support.cream.nl so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade Copernica Marketing Software  to newer
* versions in the future. If you wish to customize this module for your
* needs please refer to http://www.copernica.com/ for more information.
*
* @category    Copernica
* @package     Copernica_MarketingSoftware
* @copyright   Copyright (c) 2011-2015 Copernica & Cream. (http://docs.cream.nl/)
* @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*/

/*
 *  With 2.3.4 stat_sync events are little bit more intelligent. They now store 
 *  SyncStatus object as way of tracking progress of synchronization. Old events
 *  would cause errors cause they don't have such objects. That is why we want
 *  to regenerate start_sync event (if they are present).
 */

// get installer into local scope
$installer = $this;

// start setup
$installer->startSetup();

try {

    // we want to check if there is a synchronization scheduled
    if (Mage::helper('marketingsoftware')->isSynchronisationStartScheduled()) {
        // get old sync event
        $syncEvent = Mage::getResourceModel('marketingsoftware/queue_collection')
            ->addFieldToFilter('action', 'start_sync')->getFirstItem();

        // delete old sync event
        $syncEvent->delete();

        // create sync status object
        $syncStatus = Mage::getModel('marketingsoftware/sync_status');

        // create new sync event
        Mage::getModel('marketingsoftware/queue_item')
            ->setObject($syncStatus)
            ->setAction('start_sync')
            ->save();
    } 
} catch(Exception $e) {
    Mage::logException($e);
}

// end setup
$installer->endSetup();
