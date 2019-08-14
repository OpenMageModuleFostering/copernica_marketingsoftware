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
 *  This class should take care of all actions performed with customer object.
 *  So any modification or additions should go via this class. Also this class
 *  can sync almost all customer data in one run via ::actionFull(). Full action
 *  should be used only when there is need to that. 
 */
class Copernica_MarketingSoftware_Model_Queue_Event_Customer extends Copernica_MarketingSoftware_Model_Queue_Event_Abstract
{
    /**
     *  Handle customer add action. It's basicaly the same as modify action.
     *  
     *  @return	boolean
     */
    public function actionAdd()
    {
        return $this->actionModify();
    }

    /**
     *  Handle customer modify action. This action should be called everytime 
     *  when customer is modified. So on administrative edition or when customer
     *  himself is modifying his data.
     *
     *  @return	boolean
     */
    public function actionModify()
    {
        $customerEntity = Mage::getModel('marketingsoftware/copernica_entity_customer');
        $customerEntity->setCustomer($this->_getEntityId());

        $restCustomer = $customerEntity->getRestCustomer();
        $restCustomer->setProfile();   

        return true;
    }

    /**
     *  Handle customer full sync action. This action should be called only when
     *  we want to sync whole customer. For example initial data sync when 
     *  extension is installed for 1st time, or when changing copernica database.
     *
     *  This action is expensive both with time and resources. It should be called
     *  only when there is a need for that.
     *  
     *  @return	boolean
     */
    public function actionFull()
    {
        $customerEntity = Mage::getModel('marketingsoftware/copernica_entity_customer');
        $customerEntity->setCustomer($this->_getEntityId());

        $restCustomer = $customerEntity->getRestCustomer();
        $restCustomer->setProfile();
     
         foreach ($customerEntity->getOrders() as $orderEntity) {
         	$restOrder = $orderEntity->getRestOrder();
         	$restOrder->syncWithCustomer($customerEntity);
         }
        
         foreach($customerEntity->getWishlistItems() as $wishlistItemEntity) {
         	$restWishlistItem = $wishlistItemEntity->getRestWishlistItem();
         	$restWishlistItem->syncWithCustomer($customerEntity);
         }

        //$request->commit();

        return true;
    }

    /**
     *  This action should be called only when there is a need to remove a customer
     *  from copernica platform.
     *  
     *  @return boolean
     */
    public function actionRemove()
    {
        $object = $this->_getObject();

        if (property_exists($object, 'email') && property_exists($object, 'storeId')) {
            $store = Mage::getModel('core/store')->load($object->storeId);

            $website = $store->getWebsite();
            
            $group = $store->getGroup();

            $storeView = implode(' > ', array (
                $website->getName(), 
                $group->getName(), 
                $store->getName())
            );

            $profileCacheCollection = Mage::getModel('marketingsoftware/profile_cache')
                ->getCollection()
                ->setPageSize(1)
                ->addFieldToFilter('email', $object->email)
                ->addFieldToFilter('store_view', $storeView);
            
            $profileCache = $profileCacheCollection->getFirstItem();

            if (!$profileCache->isObjectNew()) {
            	$profileCache->delete();   
            }
        }

        if (!property_exists($object, 'profileId')) {
        	return false;
        }

        Mage::helper('marketingsoftware/rest_request')->delete('/profile/'.$object->profileId);

        return true;
    }
}
