<?php

// get installer into local scope
$installer = $this;

// start setup process
$installer->startSetup();

// there may be exceptions
try {
    // get table name for profile cache table
    $tableName = $this->getTable('marketingsoftware/profile_cache');

    // drop old table (it should be safe to drop such table)
    $installer->run("DROP TABLE IF EXISTS {$tableName}");

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

    // get queue table name
    $queueTableName = $installer->getTable('marketingsoftware/queue_item');

    /*
     *  Varien DDL does not support enum as a column type. That is why we will
     *  have to use normal sql query to modify that column.
     */

    // change action column to support upgrade action
    $installer->run("ALTER TABLE $queueTableName MODIFY action enum ('add', 'remove', 'modify', 'full', 'start_sync', 'upgrade') NOT NULL DEFAULT 'modify'");

    // We don't really need a full class to pass some simple values to object 
    // so we will use stdClass.
    $customerUpgradeObject = new stdClass;
    $customerUpgradeObject->start = null;
    
    // end setup process
    $installer->endSetup();

} catch (Exception $exception) {
    // tell magento to log exception.
    Mage::logException($exception);
}
