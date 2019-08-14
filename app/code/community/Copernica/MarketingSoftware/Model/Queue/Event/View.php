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
 *  This class will take care of syncing product view. 
 *  Everytime when user is visiting a page with a product this event is fired up.
 */
class Copernica_MarketingSoftware_Model_Queue_Event_View extends Copernica_MarketingSoftware_Model_Queue_Event_Abstract
{
    /**
     *  Tell copernica platform that certain user did view certain product
     *  @return boolean
     */
    public function actionAdd()
    {
        $object = $this->_getObject();                
        
        if (!$object->customerId || !is_numeric($object->customerId) || !$object->productId || !is_numeric($object->productId) || !$object->viewedAt || !is_numeric($object->viewedAt)) {
            return false;
        }        

        $customerEntity = Mage::getModel('marketingsoftware/copernica_entity_customer');
        $customerEntity->setCustomer($object->customerId);
        
        $productEntity = Mage::getModel('marketingsoftware/copernica_entity_product');
        $productEntity->setProduct($object->productId, $object->storeId);
        $productEntity->setTimestamp(date('Y-m-d G:i:s', $object->viewedAt));

        $restProduct = $productEntity->getRestProduct();
        $restProduct->viewedBy($customerEntity);

        return true;
    }
}