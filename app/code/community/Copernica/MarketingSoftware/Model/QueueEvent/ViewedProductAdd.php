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
 *  A wrapper object around an event
 */
class Copernica_MarketingSoftware_Model_QueueEvent_ViewedProductAdd extends Copernica_MarketingSoftware_Model_QueueEvent_Abstract
{
     /**
     *  Process this item in the queue
     *  @return boolean was the processing successfull
     */
    public function process()    
    {
        // Get the copernica API
        $api = Mage::helper('marketingsoftware/api');

        $product = $this->getObject();
        
        // Get the customer
        $productData = Mage::getModel('marketingsoftware/copernica_viewedproduct_subprofile')
                            ->setViewedProduct($product = $this->getObject())
                            ->setDirection('copernica');

        $tmpStore = Mage::getModel('core/store')->load($product->storeId);
        
        $storeView = Mage::getModel('marketingsoftware/abstraction_storeview')->setOriginal($tmpStore);

        $customer = Mage::getModel('marketingsoftware/abstraction_customer')->loadCustomer($productData->customerId());

        // get customer Id
        $customerId = Mage::helper('marketingsoftware/profile')->getCustomerCopernicaId($customer, $storeView);
                
        $profiles = $api->searchProfiles($customerId);

        // check if we have any profiles
        if (!array_key_exists('data', $profiles)) return true;
        
        // Process all the profiles
        foreach ($profiles['data'] as $profile)
        {
            // Update the profiles given the customer
            $api->updateViewedProductSubProfiles($profile['ID'], $productData);
        }

        // this viewed product is processed
        return true;
    }
}