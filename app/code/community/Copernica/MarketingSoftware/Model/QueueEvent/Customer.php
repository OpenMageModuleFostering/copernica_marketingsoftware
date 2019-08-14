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
 *  This class should take care of all actions performed with customer object.
 *  So any modification or additions should go via this class. Also this class
 *  can sync almost all customer data in one run via ::actionFull(). Full action
 *  should be used only when there is need to that. 
 */
class Copernica_MarketingSoftware_Model_QueueEvent_Customer extends Copernica_MarketingSoftware_Model_QueueEvent_Abstract
{
    /**
     *  Handle customer add action. It's basicaly the same as modify action.
     *  @return boolean
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
     *  @return boolean
     */
    public function actionModify()
    {
        // get customer entity
        $customer = new Copernica_MarketingSoftware_Model_Copernica_Entity_Customer($this->getEntityId());

        // set customer inside copernica platform
        $customer->getREST()->set();

        // get REST request into local scope
        $request = Mage::helper('marketingsoftware/RESTRequest');

        // prepare multi interface
        $request->prepare();

        // sync all customer addresses
        foreach ($customer->getAddresses() as $address) $address->getREST()->bindToCustomer($customer);

        // commit multi interface
        $request->commit();

        // we are just fine
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
     *  @return boolean
     */
    public function actionFull()
    {
        // get customer entity
        $customer = new Copernica_MarketingSoftware_Model_Copernica_Entity_Customer($this->getEntityId());

        // set customer inside copernica platform
        $customer->getREST()->set();

        // get REST request into local scope
        $request = Mage::helper('marketingsoftware/RESTRequest');

        // prepare request for multi interface
        $request->prepare();

        // sync all addresses
        foreach ($customer->getAddresses() as $address) $address->getREST()->bindToCustomer($customer);
     
        // sync add orders
        foreach ($customer->getOrders() as $order) $order->getREST()->syncWithCustomer($customer);

        // commit all changes to copernica
        $request->commit();

        // we should be just fine
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
        // get data object to local scope
        $object = $this->getObject();

        // check if we have email and store view data
        if (property_exists($object, 'email') && property_exists($object, 'store_id'))
        {
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

            // try to get a profile cache by email+store_view combination
            $profileCacheCollection = Mage::getModel('marketingsoftware/profileCache')
                ->getCollection()
                ->setPageSize(1)
                ->addFieldToFilter('email', $object->email)
                ->addFieldToFilter('store_view', $storeView);

            // get 1st item
            $profileCache = $profileCacheCollection->getFirstItem();

            // we don't need profile cache for customer that we just removed
            if (!$profileCache->isObjectNew()) $profileCache->delete();   
        }

        // if we don't have proper property we can not do anything
        if (!property_exists($object, 'profileId')) return false;

        // just ask copernica api to remove target profile
        Mage::helper('marketingsoftware/RESTRequest')->delete('/profile/'.$object->profileId);

        // we should be just fine here
        return true;
    }
}