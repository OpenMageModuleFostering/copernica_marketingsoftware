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
/** 
 *  Upgrader from version 1.1.7 to 1.1.8
 *  Copernica Marketing Software v 1.1.8
 *  October 2010
 *  http://www.copernica.com/
 */
 
$installer = $this;
$installer->startSetup();

$installer->run(
    "
INSERT INTO `{$installer->getTable('core_config_data')}` (path, value)
SELECT  'marketingsoftware/hostname', hostname
FROM    {$installer->getTable('copernica_marketingsoftware')}
WHERE   marketingsoftware_id = 1;

INSERT INTO `{$installer->getTable('core_config_data')}` (path, value)
SELECT  'marketingsoftware/username', username
FROM    {$installer->getTable('copernica_marketingsoftware')}
WHERE   marketingsoftware_id = 1;

INSERT INTO `{$installer->getTable('core_config_data')}` (path, value)
SELECT  'marketingsoftware/password', password
FROM    {$installer->getTable('copernica_marketingsoftware')}
WHERE   marketingsoftware_id = 1;

INSERT INTO `{$installer->getTable('core_config_data')}` (path, value)
SELECT  'marketingsoftware/account', account
FROM    {$installer->getTable('copernica_marketingsoftware')}
WHERE   marketingsoftware_id = 1;

INSERT INTO `{$installer->getTable('core_config_data')}` (path, value)
SELECT  'marketingsoftware/database', `database`
FROM    {$installer->getTable('copernica_marketingsoftware')}
WHERE   marketingsoftware_id = 1;

INSERT INTO `{$installer->getTable('core_config_data')}` (path, value)
SELECT  'marketingsoftware/linked_customer_fields', customerfields
FROM    {$installer->getTable('copernica_marketingsoftware')}
WHERE   marketingsoftware_id = 1;

INSERT INTO `{$installer->getTable('core_config_data')}` (path, value)
SELECT  'marketingsoftware/orders_collection_name', orderscollectionname
FROM    {$installer->getTable('copernica_marketingsoftware')}
WHERE   marketingsoftware_id = 1;

INSERT INTO `{$installer->getTable('core_config_data')}` (path, value)
SELECT  'marketingsoftware/linked_order_fields', orderfields
FROM    {$installer->getTable('copernica_marketingsoftware')}
WHERE   marketingsoftware_id = 1;

INSERT INTO `{$installer->getTable('core_config_data')}` (path, value)
SELECT  'marketingsoftware/address_collection_name', addresscollectionname
FROM    {$installer->getTable('copernica_marketingsoftware')}
WHERE   marketingsoftware_id = 1;

INSERT INTO `{$installer->getTable('core_config_data')}` (path, value)
SELECT  'marketingsoftware/linked_address_fields', addressfields
FROM    {$installer->getTable('copernica_marketingsoftware')}
WHERE   marketingsoftware_id = 1;

INSERT INTO `{$installer->getTable('core_config_data')}` (path, value)
SELECT  'marketingsoftware/order_items_collection_name', productcollectionname
FROM    {$installer->getTable('copernica_marketingsoftware')}
WHERE   marketingsoftware_id = 1;

INSERT INTO `{$installer->getTable('core_config_data')}` (path, value)
SELECT  'marketingsoftware/linked_order_item_fields', productfields
FROM    {$installer->getTable('copernica_marketingsoftware')}
WHERE   marketingsoftware_id = 1;

DROP TABLE IF EXISTS `{$installer->getTable('marketingsoftware/queue_item')}`;
    
CREATE TABLE `{$installer->getTable('marketingsoftware/queue_item')}` (
    `id` int(10) unsigned NOT NULL auto_increment,
    `object` text NOT NULL,
    `action` enum('add', 'remove', 'modify', 'full', 'start_sync') NOT NULL DEFAULT 'modify',
    `queue_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `result` text NULL,
    `result_time` timestamp NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB default CHARSET=utf8;
"
);

$installer->endSetup();
