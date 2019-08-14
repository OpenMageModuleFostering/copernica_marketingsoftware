<?php
/**
 * Copernica Marketing Software Plugin
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@cream.nl so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this plugin 
 * to newer versions in the future. If you wish to customize it for 
 * your needs please look at the documentation for more information.
 *
 * @category   Copernica
 * @package    Copernica_MarketingSoftware
 * @copyright  Copyright (c) 2012 Cream (http://www.cream.nl)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 *  A wrapper object around an event
 */
class Copernica_MarketingSoftware_Model_QueueEvent_CustomerRemove extends Copernica_MarketingSoftware_Model_QueueEvent_Abstract
{
     /**
     *  Process this item in the queue
     *  @return boolean was the processing successfull
     */
    public function process()
    {
        // Get the copernica API
        $api = Mage::getSingleton('marketingsoftware/marketingsoftware')->api();

        // Get the customer
        $customerData = Mage::getModel('marketingsoftware/copernica_profilecustomer')
                            ->setCustomer($customer = $this->getObject())
                            ->setDirection('copernica');
                            
        // Remove the profiles given the customer
        $api->removeProfiles($customerData);

        // this customer is processed
        return true;
    }
}