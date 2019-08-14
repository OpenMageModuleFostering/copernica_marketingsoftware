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
 * @copyright   Copyright (c) 2011-2012 Copernica & Cream. (http://docs.cream.nl/)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

// bring installer to local scope
$installer = $this;

try 
{
    /*
     *  It would be wise to put some indexes on profile cache. Since, it can 
     *  grown to quite huge sizes and eat up a lot of cpu power, we want to
     *  make fetching profile cache as fast as possible.
     */

    // get table name for profile cache table
    $tableName = $this->getTable('marketingsoftware/profileCache');

    // drop old table (it should be safe to drop such table)
    $installer->getConnection()->dropTable($tableName);

    /* 
     *  Create new table. Note that it will create DDL table definition. It's 
     *  required to order connection to create table with this definition at 
     *  the end of the script.
     */
    $table = $installer->getConnection()->newTable($tableName);

    // add Id column
    $table->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        'identity'  => true,
    ), 'Profile Cache Id');

    // add customer Id column
    $table->addColumn('customer_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true
    ), 'Customer Id');

    // add copernica customer Id column
    $table->addColumn('copernica_id', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'nullable'  => true
    ), 'Copernica customer Id');

    // add profile id column
    $table->addColumn('profile_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => true
    ), 'Profile Id');

    // add email column
    $table->addColumn('email', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'nullable'  => true
    ), 'Email address');

    // add store view column
    $table->addColumn('store_view', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'nullable'  => true
    ), 'Store view');

    // tell connection to create table
    $installer->getConnection()->createTable($table);

    // add index on copernica id field
    $installer->getConnection()->addKey($tableName, 'PROFILE_CACHE_COPERNICA_ID', 'copernica_id', 'index');

    // add index on email+store view fields
    $installer->getConnection()->addKey($tableName, 'PROFILE_CACHE_EMAIL_STORE', array('email', 'store_view'), 'index');

    // end installation process
    $installer->endSetup();

}
catch (Exception $e)
{
    // tell magento to log exception.
    Mage::logException($exception);
}