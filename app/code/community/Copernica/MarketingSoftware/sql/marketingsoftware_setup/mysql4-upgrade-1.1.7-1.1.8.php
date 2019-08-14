<?php
/** 
 *  Upgrader from version 1.1.7 to 1.1.8
 *  Copernica Marketing Software v 1.1.8
 *  October 2010
 *  http://www.copernica.com/
 */
 
$installer = $this;
$installer->startSetup();

$installer->run("
    ALTER TABLE {$installer->getTable('copernica_marketingsoftware')} 
    ADD login_valid ENUM('yes', 'no') NOT NULL default 'yes',
    ADD linked_fields_valid ENUM('yes', 'no') NOT NULL default 'no',
    ADD `addressfields` TEXT NOT NULL,
    ADD `addresscollectionname` VARCHAR(250) NOT NULL default 'Addresses',
    MODIFY COLUMN progressstatus VARCHAR(250) NOT NULL default 'none',
    DROP COLUMN extensionversion;
    
    UPDATE {$installer->getTable('copernica_marketingsoftware')} 
    SET progressstatus = 'none', addresscollectionname = '';
");

$installer->endSetup();