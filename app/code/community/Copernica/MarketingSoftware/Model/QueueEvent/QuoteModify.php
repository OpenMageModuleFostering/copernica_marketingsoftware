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
class Copernica_MarketingSoftware_Model_QueueEvent_QuoteModify extends Copernica_MarketingSoftware_Model_QueueEvent_Abstract
{
     /**
     *  Process this item in the queue
     *  @return boolean was the processing successfull
     */
    public function process()
    {
        // Get the copernica API and config helper
        $api = Mage::getSingleton('marketingsoftware/marketingsoftware')->api();

        // Get the subscription which has been modified
        $quote = $this->getObject();

        // is there a customer?
        if (is_object($customer = $quote->customer()))
        {
            $customerData = Mage::getModel('marketingsoftware/copernica_profilecustomer')
                            ->setCustomer($customer);
        }
        else
        {
            $customerData = Mage::getModel('marketingsoftware/copernica_profilequote')
                            ->setQuote($quote);
        }

        // The direction should be set
        $customerData->setDirection('copernica');

        // Update the profiles given the customer and return the found profiles
        $api->updateProfiles($customerData);
        $profiles = $api->searchProfiles($customerData->id());

        // iterate over the items to add them to the cart items collection
        foreach ($quote->items() as $quoteItem)
        {
            // Get the cart item data
            $cartItemData = Mage::getModel('marketingsoftware/copernica_cartitem_subprofile')
                                ->setQuoteItem($quoteItem)
                                ->setDirection('copernica')
                                ->setStatus('basket');

            // Iterate over the matching profiles and add / update the quote item
            foreach ($profiles->items as $profile)
            {
                $api->updateCartItemSubProfiles($profile->id, $cartItemData);
            }
        }

        // This quote was successfully synchronized
        return true;
    }
}