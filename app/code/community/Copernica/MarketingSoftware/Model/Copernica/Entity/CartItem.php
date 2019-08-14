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
 *  Brigde class between Copernica subprofile and magetno cart item
 */
class Copernica_MarketingSoftware_Model_Copernica_Entity_CartItem extends Copernica_MarketingSoftware_Model_Copernica_Entity_Item
{
    /**
     *  Fetch status of an item.
     *
     *  Magento does not have ability to determine if item was removed or if it's
     *  in cart that was forgotten by customer. We have to make that decistion here.
     *
     *  @return string
     */
    public function fetchStatus()
    {
        /*
         *  Get quote Id and quote model, we will need it to determine in what 
         *  kind of state item is right now
         */
        $quoteId = $this->item->getQuoteId();
        $quote = Mage::getModel('sales/quote');
        $quote->loadByIdWithoutStore($quoteId);

        // get order associated with fetched quote
        $order = Mage::getModel('sales/order')->getCollection()->addFieldToFilter('quote_id', $quoteId)->getFirstItem();

        // if order has an Id we could assume that item was ordered already
        if ($order->getId() > 0) return 'completed';

        // get forgotten carts collection
        $forgottenCollection = Mage::getResourceModel('reports/quote_collection');
        $forgottenCollection->addFieldToFilter('main_table.entity_id', $quoteId);

        // create proper timeout limit
        $timeoutLimit = new DateTime();
        $timeoutInterval = new DateInterval("PT".(int)(Mage::helper('marketingsoftware/config')->getAbandonedTimeout())."M");
        $timeoutInterval->invert = 1;
        $timeoutLimit->add($timeoutInterval);

        // we want to detect ones that are new to us in this run
        $forgottenCollection->addFieldToFilter('main_table.updated_at', array('lt' => $timeoutLimit->format("Y-m-d H:i:s")));

        // prepare collection for abandoned carts
        $forgottenCollection->prepareForAbandonedReport(array());

        // if we item's quote is considered abandoned we want to set status of that item properly
        if (count($forgottenCollection)) return 'abandoned';  

        // by default we will think that cart item is inside a basket
        return 'basket';
    }

    /**
     *  Get REST entity
     *  @return Copernica_MarketingSoftware_Model_REST_CartItem
     */
    public function getREST()
    {
        return new Copernica_MarketingSoftware_Model_REST_CartItem($this);
    }
}