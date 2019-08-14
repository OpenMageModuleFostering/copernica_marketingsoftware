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
     *  Quote object
     */
    private $quote;

    /**
     *  Id of a profile in copernica env
     *  @var int
     */
    private $profileId;

    /**
     *  Process this item in the queue
     *  @return boolean was the processing successfull
     */
    public function process()
    {
        /*
         *  We will need Api instnace, quote, customer data and target profile Id
         */
        $api = Mage::helper('marketingsoftware/api');
        $this->quote = $this->getObject();
        $customerData = $this->getCustomerData();

        // update profiles, this will create a profile if it does not exists
        $api->updateProfiles($customerData);

        // get profile Id        
        $profileId = $api->getProfileId($customerData);

        /*
         *  It's possible that we will be trying to update a customer that is not
         *  yet present in copernica database. In such situation we should create
         *  it's profile so we can use profileId for subprofiles. Thus there is
         *  no point in waiting till profile is created. Instead we will send 
         *  request to create profile and respawn this event. This way we will not
         *  be waitning and therefore we will not block other events.
         */
        if ($profileId === false)
        {

            // respawn this event with the same data
            $this->respawn();

            // we are done here
            return true;
        }

        // cache profile Id
        $this->profileId = $profileId;

        // update quote items
        $this->updateQuoteItems();
        
        // This quote was successfully synchronized
        return true;
    }

    /**
     *  Get customer data
     *  @return Copernica_MarketingSoftware_Model_Copernica_ProfileQuote|Copernica_MarketingSoftware_Model_Copernica_ProfileCustomer
     */
    private function getCustomerData()
    {
        // get customer
        $customer = $this->quote->customer(); 

        // check if we have real customer instance
        if (!is_object($customer)) return Mage::getModel('marketingsoftware/copernica_profilequote')->setQuote($this->quote)->setDirection('copernica');

        // return customer data
        return Mage::getModel('marketingsoftware/copernica_profilecustomer')->setCustomer($customer)->setDirection('copernica');
    }

    /**
     *  Update all quote items
     */
    private function updateQuoteItems()
    {
        // get Api instance
        $api = Mage::helper('marketingsoftware/api');

        // iterate over all quote items
        foreach ($this->quote->items() as $item)
        {
            // update item subprofile
            $api->updateCartItemSubProfiles($this->profileId, $this->getQuoteItemData($item));
        }
    }

    /**
     *  Get quote item data
     */
    private function getQuoteItemData($quoteItem)
    {
        return Mage::getModel('marketingsoftware/copernica_cartitem_subprofile')
            ->setQuoteItem($quoteItem)
            ->setDirection('copernica')
            ->setStatus('basket');
    }
}