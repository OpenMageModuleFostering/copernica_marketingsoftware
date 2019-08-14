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
 *  This model will be used to track all abandoned carts that we sent to copernica.
 */
class Copernica_MarketingSoftware_Model_Abandoned_Cart extends Mage_Core_Model_Abstract
{
    /**
     *  Construct abandoned cart model
     */
    protected function _construct()
    {
        $this->_init('marketingsoftware/abandoned_cart');
    }

    /**
     *  Set quote Id
     *  
     *  @param	int	$quoteID
     */
    public function setQuoteId($quoteID)
    {
        parent::setData('quote_id', $quoteID);

        $timestamp = new DateTime();
        
        parent::setData('timestamp', $timestamp->format("Y-m-d H:i:s"));

        return $this;
    }

    /**
     *  Get quote Id
     *  
     *  @return int
     */
    public function getQuoteId()
    {
        return parent::getData('quote_id');
    }
}