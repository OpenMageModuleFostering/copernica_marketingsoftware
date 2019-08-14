<?php

/**
 *  This event should take care of any action done on subscription.
 */
class Copernica_MarketingSoftware_Model_QueueEvent_Subscription extends Copernica_MarketingSoftware_Model_QueueEvent_Abstract
{
    /**
     *  Handle add action
     *  @return boolean
     */
    public function actionAdd()
    {
        return $this->actionModify();
    }

    /**
     *  Handle remove action
     *  @return boolean
     */
    public function actionRemove()
    {
        // get object
        $object = $this->getObject();

        // get profile linked fields
        $profileLinkedFields = Mage::helper('marketingsoftware/config')->getLinkedCustomerFields();

        // if properties don't exists we can not do anything useful
        if (!property_exists($object, 'email') && !property_exists($object, 'store_id')) return false;

        // get objects related to store
        $store = Mage::getModel('core/store')->load($object->store_id);
        $website = $store->getWebsite();
        $group = $store->getGroup();

        // construct store view identifier
        $storeView = implode(' > ', array (
            $website->getName(), 
            $group->getName(), 
            $store->getName())
        );

        // try to get a profile by email+store_view combination
        $profileCacheCollection = Mage::getModel('marketingsoftware/profileCache')
            ->getCollection()
            ->setPageSize(1)
            ->addFieldToFilter('email', $object->email)
            ->addFieldToFilter('store_view', $storeView);

        // get 1st item
        $profileCache = $profileCacheCollection->getFirstItem();

        // we don't need profile cache for subscriber that we just removed
        if (!$profileCache->isObjectNew()) $profileCache->delete();

        // bring request into local scope
        $request = Mage::helper('marketingsoftware/RESTRequest'); 

        // get database if
        $databaseId = Mage::helper('marketingsoftware/config')->getDatabaseId();

        // get profiles that match email + store view combination, and remove them
        $result = $request->get('/database/'.$databaseId.'/profiles', array('fields' => array(
            $profileLinkedFields['email'].'=='.$object->email,
            $profileLinkedFields['storeView'].'=='.$storeView
        )));

        // if we don't have anything to remove we are just fine
        if (!isset($result['total']) && $result['total'] == 0) return false;

        // remove all profile that match email + store view
        foreach ($result['data'] as $profile) $request->delete('/profile/'.$profile['ID']);
        
        // we are done here
        return true;
    }

    /**
     *  Handle modify action
     *  @return boolean
     */
    public function actionModify()
    {
        // get magento subscriber model
        $subscriber = Mage::getModel('newsletter/subscriber')->load($this->getEntityId());

        // we want to check if subscriber is still present in magento
        // most likely if there is no subscriber that we can fetch we have already
        // a remove event that will remove that subscriber from the copernica 
        if ($subscriber->isObjectNew()) return true;

        // construct subscription entity
        $subscription = new Copernica_MarketingSoftware_Model_Copernica_Entity_Subscription($subscriber);

        // sync subscription
        $subscription->getREST()->sync();

        // we should be just fine here
        return true;
    }
}
