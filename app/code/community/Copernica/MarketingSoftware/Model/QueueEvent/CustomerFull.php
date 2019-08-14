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
class Copernica_MarketingSoftware_Model_QueueEvent_CustomerFull extends Copernica_MarketingSoftware_Model_QueueEvent_Abstract
{
    /**
     *  The customer instance
     *  @var Copernica_MarketingSoftware_Model_Copernica_CustomerProfile
     */
    private $customer;

    /**
     *  The target profile Id
     *  @var int
     */
    private $profileId;

    /**
     * Quotes that were processed.
     *  @var array
     */
    private $processedQuotes = array();

     /**
     *  Process this item in the queue
     *  @return boolean was the processing successfull
     */
    public function process()
    {
        // Get the copernica API
        $api = Mage::helper('marketingsoftware/api');
        $this->customer = $this->getObject();

        // get customer data
        $customerData = $this->getCustomerData();

        // update/create profile 
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

        // get request to local scope
        $request = Mage::helper('marketingsoftware/RESTRequest');

        // start preparing calls
        $request->prepare();

        // update all customer addresses
        $this->updateCustomerAddresses();

        // update all customer orders
        $this->updateCustomerOrders();

        // update all customers quotes
        $this->updateCustomerQuotes();

        // execute all prepared calls
        $request->commit();

        // this was processed
        return true;
    }

    /**
     *  Get customer data
     *  @return Copernice_MarketingSoftware_Model_Copernice_ProfileCustomer
     */
    private function getCustomerData()
    {
        return Mage::getModel('marketingsoftware/copernica_profilecustomer')
            ->setCustomer($this->customer)
            ->setDirection('copernica');
    }

    /**
     *  Update all customer addresses
     */
    private function updateCustomerAddresses()
    {
        // get Api instance
        $api = Mage::helper('marketingsoftware/api');

        // iterate over all addresses
        foreach($this->customer->addresses() as $address)
        {
            $api->updateAddressSubProfiles($this->profileId, $this->getAddressData($address));
        }
    }

    /**
     *  Get address data
     *  @param  Copernica_MarketingSoftware_Model_Abstraction_Address
     *  @return Copernica_MarketingSoftware_Model_Copernice_Address_Subprofile
     */
    private function getAddressData($address)
    {
        return Mage::getModel('marketingsoftware/copernica_address_subprofile')
            ->setAddress($address)
            ->setDirection('copernica');
    }

    /**
     *  Update all customer orders
     */
    private function updateCustomerOrders()
    {
        // get Api instance
        $api = Mage::helper('marketingsoftware/api');

        // iterate over all orders
        foreach ($this->customer->orders() as $order)
        {
            // update order subprofile
            $api->updateOrderSubProfile($this->profileId, $this->getOrderData($order));

            // update all items of current order
            $this->updateOrderItems($order);

            // mark quote as processed
            $this->processedQuotes[] = $order->quoteId();
        }
    }

    /**
     *  Get orderdata
     *  @param Copernica_MarketingSoftware_Model_Abstraction_Order
     */
    private function getOrderData($order)
    {
        return Mage::getModel('marketingsoftware/copernica_order_subprofile')
            ->setOrder($order)
            ->setDirection('copernica');
    }

    /**
     *  @param Copernice_MarketingSoftware_Model_Copernica_ProfileOrder
     */
    private function updateOrderItems($order)
    {
        // get Api instance
        $api = Mage::helper('marketingsoftware/api');

        // iterate over all order items
        foreach ($order->items() as $item)
        {
            // update order items
            $api->updateOrderItemSubProfiles($this->profileId, $this->getOrderItemData($item));
        }
    }

    /**
     *  Get order item data
     *  @param  Copernica_MarketingSoftware_Model_Abstraction_Order_Item
     *  @return Copernica_MarketingSoftware_Model_Copernica_OrderItem_SubProfile
     */
    private function getOrderItemData($item)
    {
        return Mage::getModel('marketingsoftware/copernica_orderitem_subprofile')
            ->setOrderItem($item)
            ->setDirection('copernica');
    }

    /**
     *  Update customers quotes
     */
    private function updateCustomerQuotes()
    {
        // get api instance
        $api = Mage::helper('marketingsoftware/api');

        // iterate over all customer quotes
        foreach ($this->customer->quotes() as $quote)
        {
            // if quote was processed we don't want to process it again
            if (in_array($quote->id(), $this->processedQuotes)) continue;

            // iterate over all items in quote
            foreach ($quote->items() as $item)
            {
                $api->updateCartItemSubProfiles($this->profileId, $this->getQuoteItemData($item));
            }
        }
    }

    /**
     *  Get quote item data
     */
    private function getQuoteItemData($quote)
    {
        return Mage::getModel('marketingsoftware/copernica_cartitem_subprofile')
            ->setDirection('copernica')
            ->setStatus('basket')
            ->setQuoteItem($quote);
    }
}