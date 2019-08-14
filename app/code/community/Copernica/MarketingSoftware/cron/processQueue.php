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

/**
 *  This script will be able to run queue processing by itself. This way it will 
 *  be possible to run parallel processing via supervisor or similiar tools.
 */

// we have to set current dir to magento root
chdir(dirname(__FILE__));
chdir('../../../../../../');

$cliOptions = getopt('h::c::l::v::r::', array('help::', 'customer::', 'lock::', 'verbose::', 'runtime::'));

// should be display usage/help?
if (array_key_exists('h', $cliOptions) || array_key_exists('help', $cliOptions))
{
    echo "======================================================================".PHP_EOL;
    echo " Copernica Marketing Software Magento Extension".PHP_EOL;
    echo "======================================================================".PHP_EOL;
    echo "".PHP_EOL;
    echo "  Usage:".PHP_EOL;
    echo "  ./".basename(__FILE__)." [[--customer=CUSTOMER_ID|-c=CUSTOMER_ID]|[--lock|-l]] [--verbose=FORMAT|-v=FORMAT] [--remote|-r]".PHP_EOL;
    echo "  php ".basename(__FILE__)." [[--customer=CUSTOMER_ID|-c=CUSTOMER_ID]|[--lock|-l]] [--verbose=FORMAT|-v=FORMAT] [--remote|-r]".PHP_EOL;
    echo "".PHP_EOL;
    echo "  This script should be used to process queue of synchronization tasks".PHP_EOL;
    echo "".PHP_EOL;
    echo "  It's possible to supply this script with options that will influence".PHP_EOL;
    echo "  how processing is done. Note that long version of option will always".PHP_EOL;
    echo "  supress short version.".PHP_EOL;
    echo "".PHP_EOL;
    echo "  --customer  option with ID of customer that should be processed.".PHP_EOL;
    echo "".PHP_EOL;
    echo "  --lock      option will try to aqcuire a lock on 1st free customer".PHP_EOL;
    echo "              and process that customer only. This way it's possible ".PHP_EOL;
    echo "              to run multiple instances of processing script (via ".PHP_EOL;
    echo "              Supervisor or similiar software)".PHP_EOL;
    echo "".PHP_EOL;
    echo "  --runtime   minimun amount of time that script will sleep when lock".PHP_EOL;
    echo "              could not be aqcuired. By default it's set to 45 seconds".PHP_EOL;
    echo "".PHP_EOL;
    echo "  --verbose   option will force script to output result of run via one".PHP_EOL;
    echo "              of formats: TEXT or JSON".PHP_EOL;
    echo "".PHP_EOL;

    exit(); 
}

$customerId = -1;

// check if we should process events associated with one customer
if (array_key_exists('c', $cliOptions) || array_key_exists('customer', $cliOptions))
{
    $customerId = array_key_exists('c', $cliOptions) ? $cliOptions['c'] : 0;
    if (array_key_exists('customer', $cliOptions)) $customerId = $cliOptions['customer'];
}

$lock = false;

if (array_key_exists('l', $cliOptions) || array_key_exists('lock', $cliOptions))
{
    $lock = true;
}

$verbose = false;

if (array_key_exists('v', $cliOptions) || array_key_exists('verbose', $cliOptions))
{
    $verbose = array_key_exists('v', $cliOptions) ? $cliOptions['v'] : 'TEXT';
    if (array_key_exists('verbose', $cliOptions)) $verbose = $cliOptions['verbose'] ? $cliOptions['verbose'] : 'TEXT';
}

$runtime = 45;

if (array_key_exists('r', $cliOptions) || array_key_exists('runtime', $cliOptions))
{
    $runtime = array_key_exists('r', $cliOptions) ? $cliOptions['r'] : 45;
    if (array_key_exists('runtime', $cliOptions)) $runtime = $cliOptions['runtime'];
}

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

// get queue processor
$processor = Mage::getModel('marketingsoftware/QueueProcessor');

// check if we should lock our processor
if ($lock === false) $processor->processQueue($customerId);

// we should lock our processor
else {

    // try to aqcuire a lock that will be used when processing
    $lock = $processor->aqcuireLock();

    // check if we have a lock
    if ($lock === false) 
    {
        /*
         *  When this script is executed by supervisor or similiar software it 
         *  will be restarted just after it exits. To not hammer servers with 
         *  script super quick executions we will sleep for couple of seconds.
         */
        if ($runtime > 0 ) sleep($runtime);
        return;
    }

    // process queue with locking
    $processor->processWithLocking($lock);
}

// check if we should output something
if ($verbose !== false) echo $processor->fetchReport($verbose);
