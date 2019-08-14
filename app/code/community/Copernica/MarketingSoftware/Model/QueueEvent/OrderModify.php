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
class Copernica_MarketingSoftware_Model_QueueEvent_OrderModify extends Copernica_MarketingSoftware_Model_QueueEvent_Abstract
{
    /**
     *  Profile Id on copernica platform that will be updated
     *  @var int
     */
    private $profileId;

    /**
     *  Magento order 
     */
    private $order;

     /**
     *  Process this item in the queue
     *  @return boolean was the processing successfull
     */
    public function process()
    {
        /*
         *  We need to make some preparations. We will need Api, order instance,
         *  customer and target profile Id. 
         */
        $api = Mage::helper('marketingsoftware/api');
        $this->order = $this->getObject();
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

        // remove old cart items
        $api->removeOldCartItems($this->profileId, $this->order->quoteId());

        // get REST request
        $request = Mage::helper('marketingsoftware/RESTrequest');

        // start preparing calls
        $request->prepare();

        // update order subprofile with new info
        $api->updateOrderSubProfile($this->profileId, $this->getOrderData());

        // update order items
        $this->updateOrderItems();

        // update order addresses
        $this->updateOrderAddresses();

        // commit changes to API server
        $request->commit();

        // we are good
        return true;
    }

    /**
     *  Get order data
     *  @return Copernica_MarketingSoftware_Model_Copernica_Order_Subprofile
     */
    private function getOrderData()
    {
        // get order subprofile
        $orderData = Mage::getModel('marketingsoftware/copernica_order_subprofile');
        $orderData->setOrder($this->order)->setDirection('copernica');

        // return order data
        return $orderData;
    }

    /**
     *  Get customer data
     *  @return Copernica_MarketingSoftware_Model_Copernica_ProfileCustomer|Copernica_MarketingSoftware_Model_Copernica_ProfileOrder
     */
    private function getCustomerData()
    {
        // try to get customer instance from order
        $customer = $this->order->customer();

        // if we have a customer we want to return customer profile
        if (is_object($customer)) return Mage::getModel('marketingsoftware/copernica_profilecustomer')->setCustomer($customer)->setDirection('copernica');

        // we will just return order profile
        return Mage::getModel('marketingsoftware/copernica_profileorder')->setOrder($this->order)->setDirection('copernica');
    }

    /**
     *  Update order items
     */
    private function updateOrderItems()
    {
        // update all order items
        foreach ($this->order->items() as $orderItem)
        {
            // get information about current order item
            $itemData = Mage::getModel('marketingsoftware/copernica_orderitem_subprofile');
            $itemData->setOrderItem($orderItem)->setDirection('copernica');

            // update order item subprofile
            Mage::helper('marketingsoftware/api')->updateOrderItemSubProfiles($this->profileId, $itemData);
        }
    }

    /**
     *  Update order addresses
     */
    private function updateOrderAddresses()
    {
        // update all order addresses
        foreach ($this->order->addresses() as $address)
        {
            // get information about current address
            $addressData = Mage::getModel('marketingsoftware/copernica_address_subprofile');
            $addressData->setAddress($address)->setDirection('copernica');

            // update address subprofile
            Mage::helper('marketingsoftware/api')->updateAddressSubProfiles($this->profileId, $addressData);
        }
    }
}