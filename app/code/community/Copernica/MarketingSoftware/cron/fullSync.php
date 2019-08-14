#!/usr/bin/php
<?php
/**
 * Copernica Marketing Software 
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0).
 * It is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you are unable to obtain a copy of the license through the 
 * world-wide-web, please send an email to copernica@support.cream.nl 
 * so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this software 
 * to newer versions in the future. If you wish to customize this module 
 * for your needs please refer to http://www.magento.com/ for more 
 * information.
 *
 * @category     Copernica
 * @package      Copernica_MarketingSoftware
 * @copyright    Copyright (c) 2011-2015 Copernica & Cream. (http://docs.cream.nl/)
 * @license      http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

// we have to set current dir to magento root
chdir(dirname(__FILE__));
chdir('../../../../../../');

/**
 *  We need to require magento facade. Since this script should always be in same
 *  relative place we can require by relative path.
 */
require_once 'app/Mage.php';

umask(0);

if (!Mage::isInstalled()) {
	exit;
}

Mage::app('admin')->setUseSessionInUrl(false);

Mage::getConfig()->init();

$config = Mage::helper('marketingsoftware/config');
$config->setCustomerProgressStatus('1945-08-06 08:15:00');
$config->setOrderProgressStatus('1945-08-06 08:15:00');

$syncStatus = Mage::getModel('marketingsoftware/sync_status');

if ($enabledStores = $config->getEnabledStores()) {
	$syncStatus->setStoresFilter($enabledStores);
}

$queue = Mage::getModel('marketingsoftware/queue_item')
    ->setObject($syncStatus->toArray())
    ->setAction('start_sync')
    ->setName('startsync')
    ->save();