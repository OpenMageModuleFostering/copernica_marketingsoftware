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
 *  This clas will take care of all events associated with order
 */
class Copernica_MarketingSoftware_Model_QueueEvent_Order extends Copernica_MarketingSoftware_Model_QueueEvent_Abstract
{
    /**
     *  Customer entity
     *  @var Copernica_MarketingSoftware_Model_Copernica_Entity_Customer
     */
    private $customer = null;

    /**
     *  This action will be run on order modify event
     *  @return boolean
     */
    public function actionModify()
    {
        // get data object associated with this event
        $object = $this->getObject();

        // get vanilla magento order model
        $vanillaOrder = Mage::getModel('sales/order')->load($this->getEntityId());

        // create extension representation of an order
        $order = new Copernica_MarketingSoftware_Model_Copernica_Entity_Order($vanillaOrder);

        // check if we have customer Id inside data object
        if (property_exists($object, 'customer') && is_numeric($object->customer)) 
        {
            // create customer entity
            $customer = new Copernica_MarketingSoftware_Model_Copernica_Entity_Customer($object->customer);

            // sync order with customer
            return $order->getREST()->syncWithCustomer($customer);
        }

        // the order is anonymous
        else 
        {
            // data that should be always accessible
            $data = array (
                'email' => $vanillaOrder->getCustomerEmail(),
                'storeView' => $order->getStoreView(),
                'storeViewId' => $vanillaOrder->getStoreId(), 
                'firstname' => $vanillaOrder->getCustomerFirstname(),
                'lastname' => $vanillaOrder->getCustomerLastname(),
            );

            // assign middlename if we have middlename
            if ($middlename = $vanillaOrder->getCustomerMiddlename()) $data['middlename'] = $middlename;
            if ($dayOfBirth = $vanillaOrder->getCustomerDob()) $data['birthdate'] = $dateOfBirth;

            // get group
            $group = $vanillaOrder->getCustomerGroupId();
            $group = Mage::getModel('customer/group')->load($group)->getCode();

            // assign group code
            $data['group'] = $group;

            // sync with guest data
            return $order->getREST()->syncWithGuest($data);
        }
    }

    /**
     *  This action will be run on order add event
     *  @return boolean
     */
    public function actionAdd()
    {
        // we can call modify action. That will create proper order
        $this->actionModify();

        /*
         *  We should also remove all cart items from profile, since customer 
         *  did checkout with his cart items are no longer considered as ones in
         *  cart but instead, they were synced with order and are considered 
         *  bought items.
         */
        
        // we will need order model for a second
        $order = Mage::getModel('sales/order')->load($this->getEntityId());

        // get object into local scope
        $object = $this->getObject();

        // get customer
        $customer = $this->getCustomer();

        // if  we don't have a customer then it's ok. That mean the order is a 
        // guest order, so quote was never synced and thanks to that we don't have
        // to care about cart items collection
        if (!in_object($customer)) return true;

        // get request into local scope
        $request = Mage::helper('marketingsoftware/RESTRequest');

        // get cart items collection
        $cartItemsCollection = Mage::helper('marketingsoftware/config')->getCartItemsCollectionId();

        // get profiles data
        if ($cartItemsCollection) $response = $request->get('/profile/'.$customer->getProfileId().'/subprofiles/'.$cartItemsCollection, array(
            'fields' => array('quote_id=='.$order->getQuoteId())
        ));

        // check if we have data to play with
        if (array_key_exists('data', $response) || count($response['data']) == 0) return true;

        // prepare multi interface
        $request->prepare();

        /*
         *  User could decided that he want to keep information about removed 
         *  items inside profile (and mark them as 'deleted') or remove subprofiles
         *  of ordered items.
         */
        if (Mage::helper('marketingsoftware/config')->getRemoveFinishedCartItems()){
            foreach ($response['data'] as $subprofile) $request->delete('/subprofile/'.$subprofile['ID']);
        }
        else {
            foreach ($response['data'] as $subprofile) $request->put('/subprofile/'.$subprofile['ID'].'/fields/', array('status' => 'completed'));
        }

        // submit all changes to copernica platform
        $request->commit();

        // we are just ok here
        return true;
    }

    /**
     *  Get customer
     *  @return Copernica_MarketingSoftware_Model_Copernica_Entity_Customer
     */
    private function getCustomer()
    {
        if (!is_null($this->customer)) return $this->customer;

        return $this->customer = new Copernica_MarketingSoftware_Model_Copernica_Entity_Customer($object->customer);
    }
}