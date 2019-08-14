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
     *  
     *  @param	mixed	$something
     *  @param  Copernica_MarketingSoftware_Model_Abstraction_Storeview	$storeview
     */
    public function getCopernicaId($something, Copernica_MarketingSoftware_Model_Abstraction_Storeview $storeview)
    {
        switch (true) {
            // is it a customer instance? 
            case ($something instanceof Copernica_MarketingSoftware_Model_Copernica_Profile_Customer):
                return $this->getCustomerCopernicaId($something, $storeview);

            // is it a string? most likely it's an email address
            case (is_string($something)):
                return $this->getEmailCopernicaId($something, $storeview);
        }
    }

    /**
     *  Get copernica Id by customer instance
     *  
     *  @param  Copernica_MarketingSoftware_Model_Abstraction_Customer	$customer
     *  @param  Copernica_MarketingSoftware_Model_Abstraction_Storeview	$storeview
     *  @return string
     */
    public function getCustomerCopernicaId(Copernica_MarketingSoftware_Model_Abstraction_Customer $customer, Copernica_MarketingSoftware_Model_Abstraction_Storeview $storeview) 
    {
        $customerId = $customer->id();

        $profileCache = Mage::getModel('marketingsoftware/profile_cache')
            ->getCollection()
            ->addFieldToFilter('customer_id', $customerId)
            ->addFieldToFilter('store_view', $storeview->id())
            ->setPageSize(1);

        if ($profileCache->count()) {
        	return $profileCache->getFirstItem()->getCopernicaId();
        }

        $profileCache = Mage::getModel('marketingsoftware/profile_cache')
            ->getCollection()
            ->addFieldToFilter('store_view', $storeview->id())
            ->addFieldToFilter('email', $customer->email())
            ->setPageSize(1);

        if ($profileCache->count()) {
            $profileCache = $profileCache->getFirstItem();
            $profileCache->setCustomerId($customer->id());

            $this->_upgradeSubscriberCopernicaId($profileCache);

            $copernicaId = $this->generateCustomerCopernicaId($customer, $storeview);

            $this->_convertCustomerId($this->generateEmailCopernicaId($customer->email(), $storeview), $copernicaId);

            return $copernicaId;
        }

        $profileCache = $profileCache->getFirstItem();

        $copernicaId = $this->generateCustomerCopernicaId($customer, $storeview);

        $profileCache->setCopernicaId($copernicaId);
        $profileCache->setStoreView($storeview->id());
        $profileCache->setCustomerId($customer->id());
        $profileCache->save();

        $oldCopernicaId = $this->_generateOldCopernicaId($customer->oldEmail(), (string)$storeview);

        $this->_convertCustomerId($oldCopernicaId, $copernicaId);

        return $copernicaId;
    }

    /**
     *  This method will upgrade profile cache data.
     *  
     *  @param	Copernica_MarketingSoftware_Model_Profile_Cache	$profileCache
     */
    protected function _upgradeSubscriberCopernicaId(Copernica_MarketingSoftware_Model_Profile_Cache $profileCache) 
    {
        $customerId = $profileCache->getCustomerId();
        
        $storeviewId = $profileCache->getStoreView();

        $profileCache->setCopernicaId($customerId.'|'.$storeviewId);
        $profileCache->save();
    }

    /**
     *  Generate Copernica Id from our customer instance and store view
     *  
     *  @param  Copernica_MarketingSoftware_Model_Abstraction_Customer	$customer
     *  @param  Copernica_MarketingSoftware_Model_Abstraction_Storeview	$storeview
     *  @return string
     */
    public function generateCustomerCopernicaId(Copernica_MarketingSoftware_Model_Abstraction_Customer $customer, Copernica_MarketingSoftware_Model_Abstraction_Storeview $storeview) 
    {
        return $customer->id().'|'.$storeview->id();
    }

    /**
     *  Generate copernica Id from an email address and store View.
     *  
     *  @param  string	$email
     *  @param  Copernica_MarketingSoftware_Model_Abstraction_Storeview	$storeview
     *  @return string
     */
    public function getEmailCopernicaId($email, Copernica_MarketingSoftware_Model_Abstraction_Storeview $storeview) 
    {
        $profileCache = Mage::getModel('marketingsoftware/profile_cache')
            ->getCollection()
            ->addFieldToFilter('email', $email)
            ->addFieldToFilter('store_view', $storeview->id())
            ->setPageSize(1);

        if ($profileCache->count()) {
        	return $profileCache->getFirstItem()->getCopernicaId();
        }

        $profileCache = $profileCache->getFirstItem();

        $oldCopernicaId = $this->_generateOldCopernicaId($email, (string)$storeview);   

        $copernicaId = $this->generateEmailCopernicaId($email, $storeview);

        $this->_convertCustomerId($oldCopernicaId, $copernicaId);

        $profileCache->setEmail($email);
        $profileCache->setCopernicaId($copernicaId);
        $profileCache->setStoreView($storeview->id());

        $profileCache->save();

        return $copernicaId;
    }

    /**
     *  Generate Copernica Id from our email and store view
     *  
     *  @param  string	$email
     *  @param  Copernica_MarketingSoftware_Model_Abstraction_Storeview	$storeview
     *  @return	string
     */
    public function generateEmailCopernicaId($email, Copernica_MarketingSoftware_Model_Abstraction_Storeview $storeview)
    {
        return $email.'|'.$storeview->id();
    }

    /**
     *  Basically it's a helper method that will convert old customer Id in 
     *  Copernica platform into normal Magento customer Id.
     *  
     *  @param	string	$oldCopernicaId
     *  @param	string	$newCustomerId
     */
    protected function _convertCustomerId($oldCopernicaId, $newCustomerId)
    {
        $request = Mage::helper('marketingsoftware/rest_request');

        $databaseId = Mage::helper('marketingsoftware/api')->getDatabaseId();

        $request->put('database/'.$databaseId.'/profiles',
            array( 'customer_id' => $newCustomerId),
            array( 'fields[]' => 'customer_id=='.$oldCopernicaId )
        );
    }

    /**
     *  Generates a unique customer ID based on the e-mail address and the storeview.
     *
     *  @param  string	$email
     *  @param  Copernica_MarketingSoftware_Model_Abstraction_Storeview	$storeview
     *  @return string
     */
    protected function _generateOldCopernicaId($email, Copernica_MarketingSoftware_Model_Abstraction_Storeview $storeview)
    {
        return md5(strtolower($email) . $storeview);
    }
}