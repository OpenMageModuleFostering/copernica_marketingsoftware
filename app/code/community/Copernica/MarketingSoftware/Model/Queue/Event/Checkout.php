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
 *  This class should take care of all events related to checkout.
 *  
 *  This class does not matter. It should be not used cause it's same as 
 *  order modify action.
 */
class Copernica_MarketingSoftware_Model_Queue_Event_Checkout extends Copernica_MarketingSoftware_Model_Queue_Event_Abstract
{
    /**
     * Customer entity
     *
     * @var Copernica_MarketingSoftware_Model_Copernica_Entity_Customer
     */
    protected $_customerEntity;
    
    /**
     *  Process add action
     *  
     *  @return    boolean
     */
    public function actionAdd()
    {
        return $this->actionModify();
    }

    /**
     *  Modify action on checkout event
     *  
     *  @return    boolean
     */
    public function actionModify()
    {
        $customerEntity = $this->_getCustomerEntity();
        
        if (!$customerEntity) {
            return false;
        }

        $quote = Mage::getModel('sales/quote')->loadByIdWithoutStore($this->_getEntityId());
        
        $request = Mage::helper('marketingsoftware/rest_request');
        $request->prepare();

        if (Mage::helper('marketingsoftware/config')->getRemoveFinishedQuoteItem()) {
            $quoteItemCollection = Mage::helper('marketingsoftware/config')->getQuoteItemCollectionId();

            if ($quoteItemCollection) {
                $response = $request->get(
                    '/profile/'.$customerEntity->getProfileId().'/subprofiles/'.$quoteItemCollection, array(            
                    'fields' => array('quote_id=='.$this->_getEntityId())
                    )
                );
            }

            foreach ($response['data'] as $subprofile) {
                $request->delete('/subprofile/'.$subprofile['ID']);  
            }
        } else {
            foreach ($quote->getAllItems() as $quoteItem) {
                $quoteItemEntity = Mage::getModel('marketingsoftware/copernica_entity_quote_item');
                $quoteItemEntity->setQuoteItem($quoteItem);
                
                $restQuoteItem = $quoteItemEntity->getRestQuoteItem();
                $restQuoteItem->syncWithQuote($customerEntity, $quote->getId());
            }
        }

        $request->commit();

        return true;
    }
    
    protected function _getCustomerEntity()
    {
        if ($this->_customerEntity) {
            return $this->_customerEntity;
        } else {
            $object = $this->_getObject();
    
            if (property_exists($object, 'customerId') && is_numeric($object->customerId)) {
                $customerEntity = Mage::getModel('marketingsoftware/copernica_entity_customer');
                $customerEntity->setCustomer($object->customerId);
           
                return $this->_customerEntity = $customerEntity;
            } else {
                return false;
            }
        }
    }
}