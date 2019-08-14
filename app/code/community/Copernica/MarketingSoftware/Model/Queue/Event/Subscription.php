<?php

/**
 *  This event should take care of any action done on subscription.
 */
class Copernica_MarketingSoftware_Model_Queue_Event_Subscription extends Copernica_MarketingSoftware_Model_Queue_Event_Abstract
{
    /**
     *  Handle add action
     *  
     *  @return boolean
     */
    public function actionAdd()
    {
        return $this->actionModify();
    }

    /**
     *  Handle remove action
     *  
     *  @return boolean
     */
    public function actionRemove()
    {
        $object = $this->_getObject();

        $profileLinkedFields = Mage::helper('marketingsoftware/config')->getLinkedCustomerFields();

        if (!$object->email && !$object->store_id) {
        	return false;
        }

        $store = Mage::getModel('core/store')->load($object->store_id);
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

        $request = Mage::helper('marketingsoftware/rest_request'); 

        $databaseId = Mage::helper('marketingsoftware/config')->getDatabaseId();

        $result = $request->get('/database/'.$databaseId.'/profiles', array('fields' => array(
            $profileLinkedFields['email'].'=='.$object->email,
            $profileLinkedFields['storeView'].'=='.$storeView
        )));

        if (!isset($result['total']) && $result['total'] == 0) {
        	return false;
        }

        foreach ($result['data'] as $profile) {
        	$request->delete('/profile/'.$profile['ID']);
        }
        
        return true;
    }

    /**
     *  Handle modify action
     *  
     *  @return boolean
     */
    public function actionModify()
    {
        $subscriber = Mage::getModel('newsletter/subscriber')->load($this->_getEntityId());
 
        if ($subscriber->isObjectNew()) {
        	return true;
        }

        $subscriptionEntity = Mage::getModel('marketingsoftware/copernica_entity_subscription');
        $subscriptionEntity->setSubscription($subscriber);

        $restSubscription = $subscriptionEntity->getRestSubscription();
        $restSubscription->sync();

        return true;
    }
}
