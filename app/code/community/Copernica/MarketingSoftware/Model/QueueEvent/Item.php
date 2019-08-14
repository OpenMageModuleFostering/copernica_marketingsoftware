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
 *  This class will take care of syncing items
 */
class Copernica_MarketingSoftware_Model_QueueEvent_Item extends Copernica_MarketingSoftware_Model_QueueEvent_Abstract
{
    /**
     *  Process modify action
     *  @return boolean
     */
    public function actionModify()
    {
        // get object
        $object = $this->getObject();

        // check if we should sync a quote
        if (property_exists($object, 'quote')) $this->syncQuote();

        /*
         *  We could have a situation when customer is editing an abandoned cart.
         *  In such situation we have to revive that cart.
         */

        $collection = Mage::getModel('marketingsoftware/abandonedCart')->getCollection()->addFieldToFilter('quote_id', $object->quote);
        $abandonedCart = $collection->getFirstItem();

        // check if we have already abandoned cart or we just created a new model
        if ($abandonedCart->isObjectNew()) return true;

        // remove marker
        $abandonedCart->delete();

        // create event that will sync quote items and change cart status from
        // abandoned to basket
        $queue = Mage::getModel('marketingsoftware/queue')
            ->setObject()
            ->setCustomer($this->getCustomerId())
            ->setAction('modify')
            ->setName('quote')
            ->setEntityId($object->quote)
            ->save();

        // we are done here
        return true;
    }

    /**
     *  Process add action.
     *  @return boolean
     */
    public function actionAdd()
    {
        return $this->actionModify();
    }

    /**
     *  Process remove action
     *  @return boolean
     */
    public function actionRemove()
    {
        // get object into local scope
        $object = $this->getObject();

        // check if we have a quote to play with
        if (!property_exists($object, 'quote')) return false;

        // bring request into local scope
        $request = Mage::helper('marketingsoftware/RESTRequest');

        // get item Id and quote Id
        $itemId = $this->getEntityId();
        $quoteId = $object->quote;

        // get customer
        $customer = new Copernica_MarketingSoftware_Model_Copernica_Entity_Customer($object->customer);

        // if we don't have a customer we did something wrong
        if (($profileId = $customer->getProfileId()) === false) return false;

        // get cart item collection Id
        $cartItemCollectionId = Mage::helper('marketingsoftware/config')->getCartItemsCollectionId();

        // get item cart that we want to remove
        $response = $request->get('/profile/'.$profileId.'/subprofiles/'.$cartItemCollectionId, array(
            'quote_id' => $quoteId,
            'item_id' => $itemId
        ));

        // check if we have to create a subprofile that will have deleted status
        if (!array_key_exists('data', $response) || count($response['data']) == 0) {

            if (!Mage::helper('marketingsoftware/config')->getRemoveFinishedCartItems())
                $request->post('/profile/'.$profileId.'/subprofiles/'.$cartItemCollectionId, get_object_vars($object->item));

            return true;
        }

        /*
         *  User can decide to remove subprofiles of items that were removed by
         *  customer.
         */
        if (Mage::helper('marketingsoftware/config')->getRemoveFinishedCartItems())
        {
            foreach ($response['data'] as $subprofile) $request->delete('/subprofile/'.$subprofile['ID']);
        }

        // mark item as deleted
        else {
            $request->put('/profile/'.$profileId.'/subprofiles/'.$cartItemCollectionId, array(
                'status' => 'deleted'
            ), array (
                'fields' => array(
                    'quote_id=='.$quoteId,
                    'item_id=='.$itemId
                )
            ));
        }

        // we are just fine here
        return true;
    }

    /**
     *  Sync item with quote
     */
    public function syncQuote()
    {
        // get item
        $item = Mage::getModel('sales/quote_item')->load($this->getEntityId());

        /**
         *  In some cases this can still be executed even when item was removed.
         *  So we prefere to check if we just fetched new item.
         */
        if ($item->isObjectNew()) return;

        // create copernica entity
        $item = new Copernica_MarketingSoftware_Model_Copernica_Entity_CartItem($item);

        // get event object
        $object = $this->getObject();

        // get customer
        $customer = new Copernica_MarketingSoftware_Model_Copernica_Entity_Customer($object->customer);

        // load quote
        $quote = Mage::getModel('sales/quote')->loadByCustomer($object->customer);

        // sync item with quote
        $item->getREST()->syncWithQuote($customer, $quote->getId());
    }
}
