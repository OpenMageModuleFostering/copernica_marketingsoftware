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
 * @copyright    Copyright (c) 2011-2012 Copernica & Cream. (http://docs.cream.nl/)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

// bring installer to local scope
$installer = $this;

try 
{
    // start setup
    $installer->startSetup();

    // get name of the EventQueue table
    $queueTableName = $installer->getTable('marketingsoftware/queue_item');

    /* 
     *  We want to get list of distinct customers that should be synchronized.
     */
    $result = $installer->getConnection()->fetchAll("SELECT DISTINCT customer FROM $queueTableName");

    // we should remove all events that were on queue till this point
    $installer->getConnection()->query("DELETE FROM $queueTableName");

    /**
     *  Varien lib is a piece of s***. It has problems with defining text types 
     *  for mysql tables. This is why we have to check if we have our desired 
     *  type and make a fallback on some auxiliary type.
     */
    $textType = defined('Varien_Db_Ddl_Table::TYPE_TEXT') ? Varien_Db_Ddl_Table::TYPE_TEXT : Varien_Db_Ddl_Table::TYPE_VARCHAR;

    // We will need a name for our events. 
    $installer->getConnection()->addColumn($queueTableName, 'name', array(
        'comment' => 'name of the event',
        'type' => $textType,
        'length' => 255,
        'nullable' => true,
        'default' => null
    ));

    // we will need associated entity for our events
    $installer->getConnection()->addColumn($queueTableName, 'entity_id', array(
        'comment' => 'entity id associated with event',
        'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'nullable' => true,
        'default' => null
    ));

    // iterate over all unique customer that we found on old queue
    foreach ($result as $row)
    {
        // create new events to sync all unique custoemr that we found on old queue
        Mage::getModel('marketingsoftware/queue_item')
            ->setObject(null)
            ->setCustomer($row['customer'])
            ->setEntityId($row['customer'])
            ->setName('customer')
            ->setAction('full')
            ->save();
    }

    /*
     *  With this version we did also change how queue is processed. Basically 
     *  it's now possible to rely on magento cron scheduler as it was before, but
     *  als we can rely on pure executing processQueue.php script that will process
     *  queue.
     *  By default we want to set new option for that to true, so it will have
     *  to be unchecked by user to enable new functionality.
     */
    Mage::helper('marketingsoftware/config')->setVanillaCrons(true);
    
    // we are done here
    $installer->endSetup();
}

// catch any exceptions and log them as exceptions
catch(Exception $exception)
{
    Mage::log($exception->getMessage());
}