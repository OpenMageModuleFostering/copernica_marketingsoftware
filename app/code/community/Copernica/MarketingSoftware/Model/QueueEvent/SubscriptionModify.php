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
 *  A wrapper object around an event
 */
class Copernica_MarketingSoftware_Model_QueueEvent_SubscriptionModify extends Copernica_MarketingSoftware_Model_QueueEvent_Abstract
{
     /**
     *  Process this item in the queue
     *  @return boolean was the processing successfull
     */
    public function process()
    {
        // Get the copernica API
        $api = Mage::getSingleton('marketingsoftware/marketingsoftware')->api();

        // Get the subscription which has been modified
        $subscription = $this->getObject();

        // is there a customer?
        if (is_object($customer = $subscription->customer()))
        {
            // Maybe this is an old subscription which is updated, use the subscriber
            // identifier, so that the old record is updated, the typo 'subcr' should
            // remain there for backwards compatibility
            $identifier = 'subcr_'.$subscription->id();

            // get the customer data
            $customerData = Mage::getModel('marketingsoftware/copernica_profilecustomer')
                            ->setCustomer($customer);
        }
        else
        {
            // use the normal identifier
            $identifier = false;

            // get the customer data
            $customerData = Mage::getModel('marketingsoftware/copernica_profilesubscription')
                            ->setSubscription($subscription);
        }

        // The direction should be set
        $customerData->setDirection('copernica');

        // Update the profiles given the customer
        $api->updateProfiles($customerData);

        // this might result in two profiles with the same customer_id
        $profiles = $api->searchProfiles($customerData->id());

        // we received an invalid response
        if (!is_object($profiles)) return false;

        // this subscription is processed
        return true;
    }
}