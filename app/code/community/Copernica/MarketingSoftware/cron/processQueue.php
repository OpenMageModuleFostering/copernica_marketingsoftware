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

/**
 *  This script will be able to run queue processing by itself. This way it will 
 *  be possible to run parallel processing via supervisor or similiar tools.
 */

// we have to set current dir to magento root
chdir(dirname(__FILE__));
chdir('../../../../../../');

$cliOptions = getopt('h::c::l::v::r::', array('help::', 'customer::', 'lock::', 'verbose::', 'runtime::'));

if (array_key_exists('h', $cliOptions) || array_key_exists('help', $cliOptions)) {
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

try {
    $customerId = -1;

    if (array_key_exists('c', $cliOptions) || array_key_exists('customer', $cliOptions)) {
        $customerId = array_key_exists('c', $cliOptions) ? $cliOptions['c'] : 0;
        
        if (array_key_exists('customer', $cliOptions)) {
            $customerId = $cliOptions['customer'];
        }
    }

    $lock = false;

    if (array_key_exists('l', $cliOptions) || array_key_exists('lock', $cliOptions)) {
        $lock = true;
    }

    $verbose = false;

    if (array_key_exists('v', $cliOptions) || array_key_exists('verbose', $cliOptions)) {
        $verbose = array_key_exists('v', $cliOptions) ? $cliOptions['v'] : 'TEXT';
        
        if (array_key_exists('verbose', $cliOptions)) {
            $verbose = $cliOptions['verbose'] ? $cliOptions['verbose'] : 'TEXT';
        }
    }

    $runtime = 45;

    if (array_key_exists('r', $cliOptions) || array_key_exists('runtime', $cliOptions)) {
        $runtime = array_key_exists('r', $cliOptions) ? $cliOptions['r'] : 45;
        
        if (array_key_exists('runtime', $cliOptions)) {
            $runtime = $cliOptions['runtime'];
        }
    }

    require_once 'app/Mage.php';

    umask(0);

    if (!Mage::isInstalled()) {
        exit;
    }

    Mage::app('admin')->setUseSessionInUrl(false);

    Mage::getConfig()->init();

    $processor = Mage::getModel('marketingsoftware/queue_processor');

    if ($lock === false) {
        $processor->processQueue($customerId);
    } else {
        $lock = $processor->aqcuireLock();

        if ($lock === false) {
            if ($runtime > 0 ) {
                sleep($runtime);
            }
            return;
        }

        $processor->processWithLocking($lock);
    }

    if ($verbose !== false) {
        echo $processor->fetchReport($verbose);
    }
} catch (Exception $e) {
    print_r($e);
}