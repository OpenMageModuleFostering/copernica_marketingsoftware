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
 *  Brigde class between Copernica subprofile and magento quote item
 */
class Copernica_MarketingSoftware_Model_Copernica_Entity_Quote_Item extends Copernica_MarketingSoftware_Model_Copernica_Entity_Order_Item
{	
    /**
     *  Fetch status of an item.
     *
     *  Magento does not have ability to determine if item was removed or if it's
     *  in cart that was forgotten by customer. We have to make that decision here.
     *
     *  @return string
     */
    public function fetchStatus()
    { 
        $quoteId = $this->_orderItem->getQuoteId();
        
        $quote = Mage::getModel('sales/quote');
        $quote->loadByIdWithoutStore($quoteId);

        $order = Mage::getModel('sales/order')->getCollection()->addFieldToFilter('quote_id', $quoteId)->getFirstItem();

        if ($order->getId() > 0) {
        	return 'completed';
        }

        $forgottenCollection = Mage::getResourceModel('reports/quote_collection');
        $forgottenCollection->addFieldToFilter('main_table.entity_id', $quoteId);

        $timeoutLimit = new DateTime();
        $timeoutInterval = new DateInterval("PT".(int)(Mage::helper('marketingsoftware/config')->getAbandonedTimeout())."M");
        $timeoutInterval->invert = 1;
        $timeoutLimit->add($timeoutInterval);

        $forgottenCollection->addFieldToFilter('main_table.updated_at', array('lt' => $timeoutLimit->format("Y-m-d H:i:s")));
        $forgottenCollection->prepareForAbandonedReport(array());

        if (count($forgottenCollection)) {
        	return 'abandoned';  
        }

        return 'basket';
    }

    /**
     *  Get REST quote item entity
     *  
     *  @return Copernica_MarketingSoftware_Model_Rest_Quote_Item
     */
    public function getRestQuoteItem()
    {
    	$restQuoteItem = Mage::getModel('marketingsoftware/rest_quote_item');
    	$restQuoteItem->setQuoteItemEntity($this);
    	 
    	return $restQuoteItem;
    }
    
    /**
     *  Set copernica quote item
     *
     *  @param	Mage_Sales_Model_Quote_Item	$quoteItem
     */
    public function setQuoteItem(Mage_Sales_Model_Quote_Item $quoteItem)
    {
    	$this->setOrderItem($quoteItem);
    }
}