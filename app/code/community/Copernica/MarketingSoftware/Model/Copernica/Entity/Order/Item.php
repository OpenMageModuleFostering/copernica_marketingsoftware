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
 *  Bridge class between magento item and copernica subprofile
 */
class Copernica_MarketingSoftware_Model_Copernica_Entity_Order_Item extends Copernica_MarketingSoftware_Model_Copernica_Entity_Product
{
    /**
     *  Cached item
     *  
     *  @var	Mage_Sales_Model_Order_Item|Mage_Sales_Model_Quote_Item
     */
    protected $_orderItem = null;

    /**
     *  Get item Id
     *
     *  @return string
     */
    public function fetchId()
    {
    	return $this->_orderItem->getId();	
    }
    
    /**
     *  Fetch quantity
     *  
     *  @return string
     */
    public function fetchQuantity()
    {
        if ($this->_orderItem instanceOf Mage_Sales_Model_Quote_Item) {
        	return $this->_orderItem->getQty();
        }

        return $this->_orderItem->getQtyOrdered();
    }

    /**
     *  Fetch price
     *  
     *  @return Copernia_MarketingSoftware_Model_Abstraction_Price
     */
    public function fetchFullPrice()
    {
        return Mage::getModel('marketingsoftware/abstraction_price')->setOriginal($this->_orderItem);
    }

    /**
     *  Fetch total
     *  
     *  @return string
     */
    public function fetchTotalPrice()
    {
        return $this->getFullPrice()->total();
    }

    /**
     *  Fetch price
     *  
     *  @return string
     */
    public function fetchPrice()
    {
        return $this->getFullPrice()->itemPrice();
    }

    /**
     *  Fetch timestamp
     *  
     *  @return string
     */
    public function fetchTimestamp()
    {
        return $this->_orderItem->getUpdatedAt();
    }

    /**
     *  Fetch store view
     *  
     *  @return string
     */
    public function fetchStoreView()
    {
    	$store = Mage::getModel('core/store')->load($this->getStoreId());
    	
    	return Mage::getModel('marketingsoftware/abstraction_storeview')->setOriginal($store);        
    }

    /**
     *  Get store Id
     *  
     *  @return int
     */
    public function getStoreId()
    { 
        if ($this->_orderItem instanceof Mage_Sales_Model_Quote_Item) {
        	if ($this->_orderItem->getQuote()) {
        		return $this->_orderItem->getQuote()->getStoreId();
        	} else {
        		return Mage::getModel('sales/quote')->load($this->_orderItem->getQuoteId())->getStoreId();
        	}
        } elseif ($this->_orderItem instanceof Mage_Sales_Model_Order_Item) {
        	return $this->_orderItem->getOrder()->getStoreId();
        }

        return 0;
    }

    /**
     *  Fetch sales rules
     *  
     *  @return string
     */
    public function fetchSalesRules() 
    {
        return $this->_orderItem->getAppliedRuleIds();
    }

    /** 
     *  Options can be nested so this function will allow us to parse them in recursive manner. 
     *  
     *  @param	mixed	$values
     *  @param	string	$prefix
     *  @return string
     */
    protected function _stringifyOptions($values, $prefix = '')
    {
        $result = '';
        
        foreach ($values as $value) {
            if (is_array($value['value'])) {
                if (isset($value['value'][0]) && count($value['value']) == 1) {
                	$value['value'] = $value['value'][0];
                }

                $result .= $prefix.$value['label'].":\n".$this->_stringifyOptions($value['value'], $prefix.'  ');
            } else {
            	$result .= $prefix.$value['label'].":".$value['value']."\n";
            }
        }

        return $result;
    }

    /**
     *  Get REST order item entity
     *  
     *  @return Copernica_MarketingSoftware_Model_Rest_Order_Item
     */
    public function getRestOrderItem()
    {
    	$restOrderItem = Mage::getModel('marketingsoftware/rest_order_item');
    	$restOrderItem->setOrderItemEntity($this);
    	 
    	return $restOrderItem;    	
    }
    
    /**
     *  Set copernica order item
     *
     *  @param	Mage_Sales_Model_Order_Item|Mage_Sales_Model_Quote_Item	$orderItem
     */
    public function setOrderItem($orderItem)
    {
    	$this->_orderItem = $orderItem;
    	
    	$this->setProduct($orderItem->getProductId());
    }
}