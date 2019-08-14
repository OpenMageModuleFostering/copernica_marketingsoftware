<?php
/** 
 *  Upgrader from version 1.1.5 to 1.1.6
 *  Copernica Marketing Software v 1.1.8
 *  October 2010
 *  http://www.copernica.com/
 */
 
$installer = $this;
$installer->startSetup();

$installer->run("
    ALTER TABLE {$installer->getTable('copernica_marketingsoftware')} 
    ADD extensionversion VARCHAR(250) NOT NULL;
    
    UPDATE {$installer->getTable('copernica_marketingsoftware')}
    SET `extensionversion` = '1.1.6'
    WHERE 1 = 1;
");

$installer->endSetup();