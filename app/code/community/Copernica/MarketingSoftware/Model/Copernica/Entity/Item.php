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
class Copernica_MarketingSoftware_Model_Copernica_Entity_Item extends Copernica_MarketingSoftware_Model_Copernica_Entity_Product
{
    /**
     *  Cached item
     *  @var Mage_Sales_Model_Order_Item
     */
    protected $item = null;

    /**
     *  Construct item
     *  @param Mage_Sales_Model_Order_Item|Mage_Sales_Model_Quote_Item
     */
    public function __construct($item)
    {
        $this->item = $item;

        /**
         *  We have to fetch product from catalog module. It's very important to 
         *  ask catalog rather than item. Item model has also a method getProduct()
         *  thus it will fail with a fatal error only cause item can not fetch a 
         *  quote and fetch a store view from that quote. It's not that quote is 
         *  not existing, data is there and it can be accessed in certain situations
         *  thus by asking sales module directly or item (with getQuote() method)
         *  it will return new object instead proper quote. That is why we want 
         *  to ask for product directly, to not deal with all that 
         *  not-so-well-designed API.
         */
        $this->product = Mage::getModel('catalog/product')->load($item->getProductId());
    }

    /**
     *  Fetch quantity
     *  @return string
     */
    public function fetchQuantity()
    {
        if ($this->item instanceOf Mage_Sales_Model_Quote_Item) return $this->item->getQty();

        return $this->item->getQtyOrdered();
    }

    /**
     *  Fetch price
     *  @return Copernia_MarketingSoftware_Model_Abstraction_Price
     */
    public function fetchFullPrice()
    {
        return Mage::getModel('marketingsoftware/abstraction_price')->setOriginal($this->item);
    }

    /**
     *  Fetch total
     *  @return string
     */
    public function fetchTotalPrice()
    {
        return $this->getFullPrice()->total();
    }

    /**
     *  Fetch price
     *  @return string
     */
    public function fetchPrice()
    {
        return $this->getFullPrice()->itemPrice();
    }

    /**
     *  Fetch timestamp
     *  @return string
     */
    public function fetchTimestamp()
    {
        return $this->item->getUpdatedAt();
    }

    /**
     *  Fetch store view
     *  @return string
     */
    public function fetchStoreView()
    {
        // placeholder for store model
        $store = null;

        // get store Id
        $store = Mage::getModel('core/store')->load($this->getStoreId());

        // if we don't have a store we are just about done here
        if (is_null($store)) return '';

        // parse store to string
        return implode(' > ', array(
            $store->getWebsite()->getName(), 
            $store->getGroup()->getName(), 
            $store->getName())
        );
    }

    /**
     *  Get store Id 
     *  @return int
     */
    public function getStoreId()
    {
        // get store 
        if ($this->item instanceof Mage_Sales_Model_Quote_Item) return $this->item->getStoreId();
        elseif ($this->item instanceof Mage_Sales_Model_Order_Item) return $this->item->getOrder()->getStoreId();

        // let's go with admin option
        return 0;
    }

    /**
     *  Fetch options that were set with this item.
     *  @return string
     */
    public function fetchOptions()
    {
        // get all options
        $options = $this->product->getTypeInstance(true)->getOrderOptions($this->product);

        // options that we have to convert to string
        $neededOptions = array();

        /**
         *  Depending on what kind of item/situation we have we have to fetch 
         *  differente options to parse.
         */
        if (isset($options['attributes_info']))
        {
            $neededOptions = $options['attributes_info'];
        }
        elseif (isset($options['bundle_options']))
        {
            $neededOptions = $options['bundle_options'];
        }
        elseif (isset($options['options']))
        {
            $neeededOptions = $options['options'];
        }

        // stringify options and return them
        return $this->stringifyOptions($neededOptions);
    }

    /**
     *  Get ids of sales rules used for this product.
     *  @return string
     */
    public function fetchSalesRules() 
    {
        return $this->item->getAppliedRuleIds();
    }

    /** 
     *  Options ca be nested so this function will allow us to parse them in 
     *  recursive manner.
     *  @param  mixed
     *  @param  string
     *  @return string
     */
    private function stringifyOptions($values, $prefix = '')
    {
        $result = '';
        foreach ($values as $value)
        {
            if (is_array($value['value']))
            {
                if (isset($value['value'][0]) && count($value['value']) == 1) $value['value'] = $value['value'][0];

                $result .= $prefix.$value['label'].":\n".$this->stringifyOptions($value['value'], $prefix.'  ');
            }
            else $result .= $prefix.$value['label'].":".$value['value']."\n";
        }

        return $result;
    }

    /**
     *  Get REST entity
     *  @return Copernica_MarketingSoftware_Model_REST_Item
     */
    public function getREST()
    {
        return new Copernica_MarketingSoftware_Model_REST_Item($this);
    }
}