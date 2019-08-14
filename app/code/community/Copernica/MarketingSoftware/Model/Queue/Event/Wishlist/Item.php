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
 *  Event to handler wishlsit changes
 */
class Copernica_MarketingSoftware_Model_Queue_Event_Wishlist_Item extends Copernica_MarketingSoftware_Model_Queue_Event_Abstract
{
	/**
	 * Add functionality is the same as that of modify
	 * 
	 * @return boolean
	 */
	public function actionAdd()
	{
		return $this->actionModify();		
	}
	
    /**
     *  Handle modify action
     *  
     *  @return boolean
     */
    public function actionModify()
    {
    	$object = $this->_getObject();    	   
    	
    	if (!$object->wishlistItemId || !is_numeric($object->wishlistItemId) || !$object->customerId || !is_numeric($object->customerId)) {
    		return false;
    	}    	    	
    	
        $wishlistItem = Mage::getModel('wishlist/item')->load($object->wishlistItemId);

        $customerId = $object->customerId;

        $wishlistItemEntity = Mage::getModel('marketingsoftware/copernica_entity_wishlist_item');
        $wishlistItemEntity->setWishlistItem($wishlistItem);

        $customerEntity = Mage::getModel('marketingsoftware/copernica_entity_customer');
        $customerEntity->setCustomer($customerId);

        $restWishlistItem = $wishlistItemEntity->getRestWishlistItem();
        $restWishlistItem->syncWithCustomer($customerEntity);

        return true;
    }

    /**
     *  Handle wishlist removal
     *  
     *  @return boolean
     */
    public function actionRemove()
    {
    	$object = $this->getObject();
    	
    	if (!$object->customerId || !is_numeric($object->customerId)) {
    		return false;
    	}
    	
        $wishlistItemCollectionId = Mage::helper('marketingsoftware/config')->getWishlistItemCollectionId();

        $customerEntity = Mage::getModel('marketingsoftware/copernica_entity_customer');
        $customerEntity->setCustomer($object->customerId);

        $profileId = Mage::helper('marketingsoftware/api')->getProfileId(array(
            'id' => $customerEntity->fetchId(),
            'storeView' => $customerEntity->fetchStoreView(),
            'email' => $customerEntity->fetchEmail()
        ));

        $request = Mage::helper('marketingsoftware/rest_request');

        $result = $request->get('/profile/'.$profileId.'/subprofiles/'.$wishlistItemCollectionId, array(
            'fields' => array(
                'item_id=='.$this->_getEntityId()
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
