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
 * @copyright    Copyright (c) 2011-2012 Copernica & Cream. (http://docs.cream.nl/)
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

// remove current mask
umask(0);

// if magento is not installed we will just exit this scrtipt
if (!Mage::isInstalled()) exit;

// don't use sessions
Mage::app('admin')->setUseSessionInUrl(false);

// init config
Mage::getConfig()->init();

// get config helper
$config = Mage::helper('marketingsoftware/config');

// set customer progress status to date when a-bomb hit Hiroshima. 
// we can be quite certain that no magento webshop was set up during that 
// time.
$config->setCustomerProgressStatus('1945-08-06 08:15:00');
$config->setOrderProgressStatus('1945-08-06 08:15:00');

// create sync status object
$syncStatus = Mage::getModel('marketingsoftware/SyncStatus');

// check if current configuration is telling us to filter stores
if ($enabledStores = $config->getEnabledStores()) $syncStatus->setStoresFilter($enabledStores);

// The start sync token must be added to the queue
$queue = Mage::getModel('marketingsoftware/queue')
    ->setObject($syncStatus->toArray())
    ->setAction('start_sync')
    ->setName('startSync')
    ->save();