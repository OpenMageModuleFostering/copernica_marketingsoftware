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
 * @copyright    Copyright (c) 2011-2015 Copernica & Cream. (http://docs.cream.nl/)
 * @license      http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 *  This class will take care of syncing items
 */
class Copernica_MarketingSoftware_Model_Queue_Event_Item extends Copernica_MarketingSoftware_Model_Queue_Event_Abstract
{
    /**
     *  Process modify action
     *  
     *  @return boolean
     */
    public function actionModify()
    {
        $object = $this->_getObject();        

        if (!$object->quoteId || !is_numeric($object->quoteId)) {
            return false;
        }
        
        if ($object->customerId && is_numeric($object->customerId) ) {
            $this->syncQuote();
        }

        $collection = Mage::getModel('marketingsoftware/abandoned_cart')->getCollection()->addFieldToFilter('quote_id', $object->quoteId);
        
        $abandonedCart = $collection->getFirstItem();

        if ($abandonedCart->isObjectNew()) {
            return true;
        }               

        $abandonedCart->delete();

        $queue = Mage::getModel('marketingsoftware/queue_item')
            ->setObject(array('quoteId' => $object->quoteId))
            ->setCustomer($object->customerId)
            ->setAction('modify')
            ->setName('quote')
            ->setEntityId($object->quoteId)
            ->save();

        return true;
    }

    /**
     *  Process add action.
     *  
     *  @return boolean
     */
    public function actionAdd()
    {
        return $this->actionModify();
    }

    /**
     *  Process remove action
     *  
     *  @return boolean
     */
    public function actionRemove()
    {
        $object = $this->_getObject();

        if (!$object->quoteId || !is_numeric($object->quoteId) || !$object->customerId || !is_numeric($object->customerId) || !$object->quoteItem) {
            return false;
        }        
        
        $quoteItemData = get_object_vars($object->quoteItem);
        
        $request = Mage::helper('marketingsoftware/rest_request');
        
        $itemId = $quoteItemData['item_id'];
        
        $quoteId = $object->quoteId;        
        
        $store = Mage::getModel('core/store')->load($quoteItemData['storeview_id']);
        
        $storeview = Mage::getModel('marketingsoftware/abstraction_storeview')->setOriginal($store);
        
        $customerEntity = Mage::getModel('marketingsoftware/copernica_entity_customer');
        $customerEntity->setCustomer($object->customerId);
        $customerEntity->setStore($storeview);

        if (($profileId = $customerEntity->getProfileId()) === false) {
            return false;
        }
        
        $quoteItemCollectionId = Mage::helper('marketingsoftware/config')->getQuoteItemCollectionId();

        
        $response = $request->get(
            '/profile/'.$profileId.'/subprofiles/'.$quoteItemCollectionId, array(
            'item_id' => $itemId,
            'quote_id' => $quoteId,            
            )
        );
        
        if (!array_key_exists('data', $response) || count($response['data']) == 0) {
            if (!Mage::helper('marketingsoftware/config')->getRemoveFinishedQuoteItem()) {
                $request->post('/profile/'.$profileId.'/subprofiles/'.$quoteItemCollectionId, $quoteItemData);
            }                

            return true;
        }

        if (Mage::helper('marketingsoftware/config')->getRemoveFinishedQuoteItem()) {
            foreach ($response['data'] as $subprofile) {
                $request->delete('/subprofile/'.$subprofile['ID']);
            }
        } else {
            $request->put(
                '/profile/'.$profileId.'/subprofiles/'.$quoteItemCollectionId, array(
                'status' => 'deleted'
                ), array (
                'fields' => array(
                    'quote_id=='.$quoteId,
                    'item_id=='.$itemId
                )
                )
            );
        }

        return true;
    }

    /**
     *  Sync item with quote
     */
    public function syncQuote()
    {
        $object = $this->_getObject();

        if (!$object->customerId || !is_numeric($object->customerId)) {
            return false;
        }        
        
        if ($object->quoteId && is_numeric($object->quoteId)) {
            $quoteId = $object->quoteId;
        } else {
            $quote = Mage::getModel('sales/quote')->loadByCustomer($object->customerId);
            $quoteId = $quote->getId();
        }
        
        $quoteItem = Mage::getModel('sales/quote_item')->load($object->quoteItemId);
        
        if ($quoteId && $quoteItem->getId()) {
            $customerEntity = Mage::getModel('marketingsoftware/copernica_entity_customer');
            $customerEntity->setCustomer($object->customerId);
            
            $quoteItemEntity = Mage::getModel('marketingsoftware/copernica_entity_quote_item');
            $quoteItemEntity->setQuoteItem($quoteItem);       
    
            $restQuoteItem = $quoteItemEntity->getRestQuoteItem();
            $restQuoteItem->syncWithQuote($customerEntity, $quoteId);
        }
    }
}
