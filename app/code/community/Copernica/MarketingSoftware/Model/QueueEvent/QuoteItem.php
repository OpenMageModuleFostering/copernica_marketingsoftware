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
abstract class Copernica_MarketingSoftware_Model_QueueEvent_QuoteItem extends Copernica_MarketingSoftware_Model_QueueEvent_Abstract
{
    /**
     *  Quote Item
     */
    private $quoteItem;

     /**
     *  Process this item in the queue
     *  @return boolean was the processing successfull
     */
    public function process()
    {
        // Get the copernica API and config helper
        $api = Mage::helper('marketingsoftware/api');

        // Get the subscription which has been modified
        $this->quoteItem = $this->getObject();

        // Get the customer
        $customerData = $this->getCustomerData();

        // if we don't have customer data we don't really care about this item
        if ($customerData === false) return true;

        // get profile Id
        $profileId = $api->getProfileId($customerData);
        
        // Get the profiles from the api
        $api->updateProfiles($customerData);

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

        // update cart item subprofile
        $api->updateCartItemSubProfiles($profileId, $this->getCartItemData());

        // all went allright
        return true;
    }

    /**
     *  Get customer data
     *  @return Copernica_MarketingSoftware_Model_Copernica_ProfileCustomer
     */
    private function getCustomerData()
    {
        // get and prepare customer data
        $customerData = Mage::getModel('marketingsoftware/copernica_profilecustomer');

        // get the customer from the quote
        $customer = $this->quoteItem->quote()->customer();

        // if we don't have customer we can not return customer data (that makes sense...)
        if (!is_object($customer)) return false;

        // set customer and direction
        $customerData->setCustomer($customer)->setDirection('copernica');

        // return customer data
        return $customerData;
    }

    /**
     *  Get cart item data
     *  @return Copernica_MarketingSoftware_Model_Copernica_CartItemSubprofile
     */ 
    private function getCartItemData()
    {
        return Mage::getModel('marketingsoftware/copernica_cartitem_subprofile')
            ->setQuoteItem($this->quoteItem)
            ->setDirection('copernica')
            ->setStatus($this->status());
    }

    /**
     *  In what status is this cart item
     *  @return String
     */
    abstract protected function status();
}