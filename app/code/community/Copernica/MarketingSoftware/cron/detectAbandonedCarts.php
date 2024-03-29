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

/*
 *  This script is mainly for testing. It will enforce detection of forgotten carts.
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

$observer = Mage::getModel('marketingsoftware/observer');
$observer->detectAbandonedCarts();