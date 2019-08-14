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
 *  This class will help with customer-profile relation.
 *
 *  Profiles in copernica plarform represents either a customer in store view, 
 *  or email in store view. It's important to understand that there could be more 
 *  than one profile for one customer. It's cause one customer can be in more 
 *  than one store view. Same applies to email addresses cause of same reasons.
 */
class Copernica_MarketingSoftware_Helper_Profile 
{
    /**
     *  Get copernica Id based on something and store view.
     *  @param  mixed
     *  @param  Copernica_MarketingSoftware_Model_Abstraction_StoreView
     */
    public function getCopernicaId($something, $storeView)
    {
        switch (true) {
            // is it a customer instance? 
            case ($something instanceof Copernica_MarketingSoftware_Model_Copernica_Customer):
                return $this->getCustomerCopernicaId($something, $storeView);

            // is it a string? most likely it's an email address
            case (is_string($something)):
                return $this->getEmailCopernicaId($something, $storeView);
        }
    }

    /**
     *  Get copernica Id by customer instance
     *  @param  Copernica_MarketingSoftware_Model_Copernica_Customer
     *  @param  Copernica_MarketingSoftware_Model_Abstraction_StoreView
     *  @return string
     */
    public function getCustomerCopernicaId($customer, $storeView) 
    {
        // get customer Id
        $customerId = $customer->id();

        // try to get a profile cache that matches customer and store view
        $profileCache = Mage::getModel('marketingsoftware/profileCache')
            ->getCollection()
            ->addFieldToFilter('customer_id', $customerId)
            ->addFieldToFilter('store_view', $storeView->id())
            ->setPageSize(1);

        // return copernica Id
        if ($profileCache->count()) return $profileCache->getFirstItem()->getCopernicaId();

        /*
         *  Since same customer could be already subscribed to newsletter, we 
         *  want to check if we have an entry that matches customer email address.
         */

        $profileCache = Mage::getModel('marketingsoftware/profileCache')
            ->getCollection()
            ->addFieldToFilter('store_view', $storeView->id())
            ->addFieldToFilter('email', $customer->email())
            ->setPageSize(1);

        // check if we have some cached profiles
        if ($profileCache->count()) 
        {
            // we want a specific item
            $profileCache = $profileCache->getFirstItem();

            // set customer Id
            $profileCache->setCustomerId($customer->id());

            // upgrade subscriber copernica Id
            $this->upgradeSubscriberCopernicaId($profileCache);

            // generate copernica Id
            $copernicaId = $this->generateCustomerCopernicaId($customer, $storeView);

            // convert old customer Id on copernica platform
            $this->convertCustomerId($this->generateEmailCopernicaId($customer->email(), $storeView), $copernicaId);

            // return new customer copernica Id
            return $copernicaId;
        }

        // we want a specific item
        $profileCache = $profileCache->getFirstItem();

        // generate copernica Id
        $copernicaId = $this->generateCustomerCopernicaId($customer, $storeView);

        // set copernica Id
        $profileCache->setCopernicaId($copernicaId);
        $profileCache->setStoreView($storeView->id());
        $profileCache->setCustomerId($customer->id());

        // store profile cache instance
        $profileCache->save();

        // generate old copernica Id
        $oldCopernicaId = $this->generateOldCopernicaId($customer->oldEmail(), (string)$storeView);

        // We want to convert old coeprnica Id with new one.
        $this->convertCustomerId($oldCopernicaId, $copernicaId);

        // return copernica Id
        return $copernicaId;
    }

    /**
     *  This method will upgrade profile cache data.
     *  @param  Copernica_MarketingSoftware_Model_ProfileCache
     */
    private function upgradeSubscriberCopernicaId($profileCache) 
    {
        // get customer Id and store view
        $customerId = $profileCache->getCustomerId();
        $storeViewId = $profileCache->getStoreView();

        // set new copernica Id
        $profileCache->setCopernicaId($customerId.'|'.$storeViewId);

        // save profile cache right away
        $profileCache->save();
    }

    /**
     *  Generate Copernica Id from our customer instance and store view
     *  @param  Copernica_MarketingSoftware_Abstraction_Customer
     *  @param  Copernica_MarketingSoftware_Abstraction_StoreView
     *  @return string
     */
    public function generateCustomerCopernicaId($customer, $storeView) 
    {
        return $customer->id().'|'.$storeView->id();
    }

    /**
     *  Generate copernica Id from an email address and store View.
     *  @param  string
     *  @param  Copernica_MarketingSoftware_Abstraction_StoreView
     *  @return string
     */
    public function getEmailCopernicaId($email, $storeView) 
    {
        // fetch profile cache
        $profileCache = Mage::getModel('marketingsoftware/profileCache')
            ->getCollection()
            ->addFieldToFilter('email', $email)
            ->addFieldToFilter('store_view', $storeView->id())
            ->setPageSize(1);

        // return copernica Id
        if ($profileCache->count()) return $profileCache->getFirstItem()->getCopernicaId();

        // we want a specific entity
        $profileCache = $profileCache->getFirstItem();

        // get old copernica Id
        $oldCopernicaId = $this->generateOldCopernicaId($email, (string)$storeView);   

        // generate copernica Id from email and store view
        $copernicaId = $this->generateEmailCopernicaId($email, $storeView);

        // convert profiles in copernica platform also
        $this->convertCustomerId($oldCopernicaId, $copernicaId);

        // set data for profile cache
        $profileCache->setEmail($email);
        $profileCache->setCopernicaId($copernicaId);
        $profileCache->setStoreView($storeView->id());

        // save profile cache entry
        $profileCache->save();

        // return copernica Id
        return $copernicaId;
    }

    /**
     *  Generate Copernica Id from our email and store view
     *  @param  Copernica_MarketingSoftware_Model_Copernica_Customer
     *  @param  Copernica_MarketingSoftware_Model_Abstraction_StoreView
     *  @return string
     */
    public function generateEmailCopernicaId($email, $storeView)
    {
        return $email.'|'.$storeView->id();
    }

    /**
     *  Basically it's a helper method that will convert old customer Id in 
     *  Copernica platform into normal Magento customer Id.
     *  @param  string
     *  @param  string
     */
    private function convertCustomerId($oldCopernicaId, $newCustomerId)
    {
        // we will need a REST request instance
        $request = Mage::helper('marketingsoftware/RESTRequest');

        // get database Id
        $databaseId = Mage::helper('marketingsoftware/Api')->getDatabaseId();

        // we want to update profile with new customer Id
        $request->put('database/'.$databaseId.'/profiles',
            array( 'customer_id' => $newCustomerId),
            array( 'fields[]' => 'customer_id=='.$oldCopernicaId )
        );
    }

    /**
     *  Generates a unique customer ID based on the e-mail address and the storeview.
     *
     *  @param  string 
     *  @param  string 
     *  @return string
     */
    private function generateOldCopernicaId($email, $storeview)
    {
        return md5(strtolower($email) . $storeview);
    }
}