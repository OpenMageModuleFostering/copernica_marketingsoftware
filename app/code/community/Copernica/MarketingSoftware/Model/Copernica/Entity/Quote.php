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
     *  @var Mage_Sales_Model_Quote
     */
    private $quote;

    /**
     *  Cached quote items
     *  @var array
     */
    private $items;

    /**
     *  Construct quote entity
     *  @param Mage_Sales_Model_Quote
     */
    public function __construct($quote)
    {
        $this->quote = $quote;
    }

    /**
     *  @return array
     */
    public function getItems()
    {
        // check if we already fetched all items 
        if (!is_null($this->items)) return $this->items;

        // data holder for quote items
        $items = array();

        // convert all items into copernica entities
        foreach ($this->quote->getAllItems() as $item) $items[] = new Copernica_MarketingSoftware_Model_Copernica_Entity_CartItem($item);

        // cache and return items
        return $this->items = $items;
    }

    /**
     *  Get RESTEntity for this quote.
     *  @return Copernica_MarketingSoftware_Model_REST_Quote
     */ 
    public function getREST()
    {
        return new Copernica_MarketingSoftware_Model_REST_Quote($this);
    }
}