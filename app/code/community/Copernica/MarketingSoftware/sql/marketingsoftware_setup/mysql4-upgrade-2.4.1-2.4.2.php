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

// bring installer to local scope
$installer = $this;

try 
{
    // start setup
    $installer->startSetup();

    // get table name for sync profile table
    $tableName = $this->getTable('marketingsoftware/sync_profile');

    // drop old table (it should be safe to drop such table)
    $installer->run("DROP TABLE IF EXISTS {$tableName}");

    /* 
     *  Create new table. Note that it will create DDL table definition. It's 
     *  required to order connection to create table with this definition at 
     *  the end of the script.
     */
    $table = $installer->getConnection()->newTable($tableName);

    // add Id column
    $table->addColumn(
        'id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        'identity'  => true,
        ), 'Sync profile Id'
    );

    // add customer Id column
    $table->addColumn(
        'client_key', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'nullable'  => false
        ), 'Copernica client key'
    );

    // add copernica customer Id column
    $table->addColumn(
        'client_secret', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'nullable'  => false
        ), 'Copernica client secret'
    );

    // add profile id column
    $table->addColumn(
        'access_token', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'nullable'  => true
        ), 'Client access token'
    );

    // add email column
    $table->addColumn(
        'name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'nullable'  => false
        ), 'Name of the sync profile'
    );

    // tell connection to create table
    $installer->getConnection()->createTable($table);

    // end setup
    $installer->endSetup();
} 
catch (Exception $e) 
{
    // tell magento to log exception.
    Mage::logException($exception);
}
