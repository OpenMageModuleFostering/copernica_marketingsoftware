<?php
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$installer->run("
DROP TABLE IF EXISTS `{$installer->getTable('marketingsoftware/queue')}`;
CREATE TABLE `{$installer->getTable('marketingsoftware/queue')}` (
    `id` int(10) unsigned NOT NULL auto_increment,
    `object` text NOT NULL,
    `action` enum('add', 'remove', 'modify','full', 'start_sync') NOT NULL DEFAULT 'modify',
    `queue_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `result` text NULL,
    `result_time` timestamp NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB default CHARSET=utf8;
");

$installer->endSetup();