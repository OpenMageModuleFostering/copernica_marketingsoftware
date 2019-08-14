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

// construct new request
$request = Mage::helper('marketingsoftware/RESTRequest');

// get current database Id
$databaseId = Mage::helper('marketingsoftware/Config')->getDatabaseId();

// get profiles
$profiles = $request->get('database/'.$databaseId.'/profiles');

// iterate over all profiles and remove each of them
foreach ($profiles['data'] as $profile) $request->delete('profile/'.$profile['ID']);


