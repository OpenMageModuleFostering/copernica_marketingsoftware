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

// start setup
$installer = $this;
$installer->startSetup();

try {
    // create profile cache table
    $installer->run(
        "
        DROP TABLE IF EXISTS `{$installer->getTable('marketingsoftware/profile_cache')}`;
        CREATE TABLE `{$installer->getTable('marketingsoftware/profile_cache')}` (
            `id` int(10) unsigned auto_increment,
            `customer_id` varchar(255) NOT NULL,
            `profile_id` int(10) unsigned NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB default CHARSET=utf8;
    "
    );

    // finalize setup
    $installer->endSetup();
} catch(Exception $e) {
    Mage::logException($e);
}

