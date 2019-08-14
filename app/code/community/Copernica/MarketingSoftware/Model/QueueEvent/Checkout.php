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
 *  This class should take care of all events related to checkout.
 *  
 *  This class does not matter. It should be not used cause it's same as 
 *  order modify action.
 */
class Copernica_MarketingSoftware_Model_QueueEvent_Checkout extends Copernica_MarketingSoftware_Model_QueueEvent_Abstract
{
    /**
     *  Process add action
     *  @return boolean
     */
    public function actionAdd()
    {
        return $this->actionModify();
    }

    /**
     *  Modify action on checkout event
     *  @return boolean
     */
    public function actionModify()
    {
        // get data object associated with this event
        $object = $this->getObject();           

        // check if we have customer Id inside data object
        if (!property_exists($object, 'customer')) return false;

        // create customer entity
        $customer = new Copernica_MarketingSoftware_Model_Copernica_Entity_Customer($object->customer);

        // get matento quote
        $quote = Mage::getModel('sales/quote')->loadByIdWithoutStore($this->getEntityId());

        // bring request  to local scope
        $request = Mage::helper('marketingsoftware/RESTRequest');

        // we want to sync all items at once
        $request->prepare();

        if (Mage::helper('marketingsoftware/config')->getRemoveFinishedCartItems())
        {
            // get cart items collection
            $cartItemsCollection = Mage::helper('marketingsoftware/config')->getCartItemsCollectionId();

            // get profiles data
            if ($cartItemsCollection) $response = $request->get('/profile/'.$customer->getProfileId().'/subprofiles/'.$cartItemsCollection, array(
                'fields' => array('quote_id=='.$this->getEntityId())
            ));

            foreach ($response['data'] as $subprofile) $request->delete('/subprofile/'.$subprofile['ID']);  
        }
        else
        {
            // sync items
            foreach ($quote->getAllVisibleItems() as $item)
            {
                $item = new Copernica_MarketingSoftware_Model_Copernica_Entity_CartItem($item);
                $item->getREST()->syncWithQuote($customer, $quote->getId());
            }
        }

        // commit request
        $request->commit();

        // we are done here
        return true;
    }
}