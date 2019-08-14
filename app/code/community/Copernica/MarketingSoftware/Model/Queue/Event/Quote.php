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
 *  Event to handler quote changes
 */
class Copernica_MarketingSoftware_Model_Queue_Event_Quote extends Copernica_MarketingSoftware_Model_Queue_Event_Abstract
{
    /**
     *  Handle modify action
     *  
     *  @return boolean
     */
    public function actionModify()
    {
        $quote = Mage::getModel('sales/quote');
        $quote->loadByIdWithoutStore($this->_getEntityId());

        $customerId = $quote->getCustomerId();

        $quoteEntity = Mage::getModel('marketingsoftware/copernica_entity_quote');
        $quoteEntity->setQuote($quote);

        $customerEntity = Mage::getModel('marketingsoftware/copernica_entity_customer');
        $customerEntity->setCustomer($customerId);

        $restQuote = $quoteEntity->getRestQuote();
        $restQuote->syncWithCustomer($customerEntity);

        return true;
    }

    /**
     *  Handle quote removal
     *  
     *  @return boolean
     */
    public function actionRemove()
    {
    	$object = $this->_getObject();
    	
    	if (!$object->customerId || !is_numeric($object->customerId) || !$object->storeView) {
    		return false;
    	}
    	
        $quoteItemCollectionId = Mage::helper('marketingsoftware/config')->getQuoteItemCollectionId();

        $customerEntity = Mage::getModel('marketingsoftware/copernica_entity_customer');
        $customerEntity->setCustomer($object->customerId);

        $profileId = Mage::helper('marketingsoftware/api')->getProfileId(array(
            'id' => $customerEntity->fetchId(),
            'storeView' => $object->storeView,
            'email' => $customerEntity->fetchEmail()
        ));

        $request = Mage::helper('marketingsoftware/rest_request');

        $result = $request->get('/profile/'.$profileId.'/subprofiles/'.$quoteItemCollectionId, array(
            'fields' => array(
                'quote_id=='.$this->_getEntityId()
            )
        ));

        $request->prepare();

        if (array_key_exists('data', $result) && is_array($result['data'])) foreach ($result['data'] as $item) {
            $request->delete('/subprofile/'.$item['ID']);
        }

        $request->commit();
        
        return true;
    }
}
