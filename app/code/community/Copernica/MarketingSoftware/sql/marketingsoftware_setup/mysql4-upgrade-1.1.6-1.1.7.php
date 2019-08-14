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
* @copyright    Copyright (c) 2011-2012 Copernica & Cream. (http://docs.cream.nl/)
* @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*/
/** 
 *  Upgrader from version 1.1.5 to 1.1.6
 *  Copernica Marketing Software v 1.1.8
 *  October 2010
 *  http://www.copernica.com/
 */
 
$installer = $this;
$installer->startSetup();

$installer->run("
    UPDATE {$installer->getTable('copernica_marketingsoftware')}
    SET `extensionversion` = '1.1.7'
    WHERE 1 = 1;
");

$installer->endSetup();