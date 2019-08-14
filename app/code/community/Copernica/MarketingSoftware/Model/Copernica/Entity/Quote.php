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
 *  Bridge between magento quote and copernica subprofile
 */
class Copernica_MarketingSoftware_Model_Copernica_Entity_Quote extends Copernica_MarketingSoftware_Model_Copernica_Entity
{
    /**
     *  Cached quote instance
     *  
     *  @var	Mage_Sales_Model_Quote
     */
    protected $_quote;

    /**
     *  Cached quote items
     *  
     *  @var	array
     */
    protected $_quoteItems;

    /**
     *  @return array
     */
    public function getItems()
    {
        if (!is_null($this->$_quoteItems)) {
        	return $this->$_quoteItems;
        }

        $quoteItems = array();

        foreach ($this->_quote->getAllItems() as $quoteItem) {
        	$quoteItemEntity = Mage::getModel('marketingsoftware/copernica_entity_quote_item');
        	$quoteItemEntity->setQuoteItem($quoteItem);
        	        	
        	$quoteItems[] = $quoteItemEntity;
        }

        return $this->_quoteItems = $quoteItems;
    }

    /**
     *  Get REST quote entity
     *  
     *  @return Copernica_MarketingSoftware_Model_Rest_Quote
     */ 
    public function getRestQuote()
    {
    	$restQuote = Mage::getModel('marketingsoftware/rest_quote');
    	$restQuote->setQuoteEntity($this);
    	 
    	return $restQuote;
    }
    
    /**
     *  Set quote entity
     *
     *  @param	Mage_Sales_Model_Quote	$quote
     */
    public function setQuote($quote)
    {
    	$this->_quote = $quote;
    }
}