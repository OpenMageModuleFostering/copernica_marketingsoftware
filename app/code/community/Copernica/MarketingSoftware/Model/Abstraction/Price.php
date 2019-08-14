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
 *  A wrapper object around a price
 *  This is not representing a magento object
 *  Note that the price can consist of a lot of components:
 *      -   price
 *      -   shippingcost
 *      -   discount
 *      -   additional_fees (is a list of custom configurable fees)
 *      -   tax
 *      -   total_price
 */
class Copernica_MarketingSoftware_Model_Abstraction_Price implements Serializable
{
    /**
     * Predefine the internal fields
     */
    protected $total;
    protected $costs;
    protected $itemPrice;
    protected $originalPrice;
    protected $discount;
    protected $tax;
    protected $shipping;
    protected $currency;

    /**
     *  Sets the original model
     *  @param      Mage_Sales_Model_Quote|Mage_Sales_Model_Order|Mage_Sales_Model_Quote_Item|Mage_Sales_Model_Order_Item $original
     *  @return     Copernica_MarketingSoftware_Model_Abstraction_Price
     */
    public function setOriginal($original)
    {
        if ($grandTotal = $original->getGrandTotal()) {
            $this->total = $grandTotal;
        } elseif ($rowTotalInclTax = $original->getRowTotalInclTax()) {
            $this->total = $rowTotalInclTax;
        } elseif ($baseRowTotal = $original->getBaseRowTotal()) {
            $this->total = $baseRowTotal;
        }
        
        // Used for quotes and orders
        if ($original instanceOf Mage_Sales_Model_Quote || $original instanceOf Mage_Sales_Model_Order) {
            $costs = 0;
        
            // iterate over all visisble items
            foreach ($original->getAllVisibleItems() as $item) {
                $costs += $item->getBaseCost();
            }
        
            // return the costs
            $this->costs = $costs;
        } elseif ($baseCost = $original->getBaseCost()) {
            $this->costs = $baseCost;
        }
        
        // no price for an individual item
        if ($original instanceOf Mage_Sales_Model_Quote || $original instanceOf Mage_Sales_Model_Order)
            $this->itemPrice = 0;
        
        // Used for quote items and order items
        elseif ($price = $original->getPrice())
            $this->itemPrice = $price;
        
        // no price for an individual item
        if ($original instanceOf Mage_Sales_Model_Quote || $original instanceOf Mage_Sales_Model_Order)
            $this->originalPrice = 0;

        // it's not safe to call ::getOriginalPrice() on quote item
        elseif ($original instanceOf Mage_Sales_Model_Quote_Item)
            $this->originalPrice = 0;

        // Used for quote items and order items
        elseif ($originalPrice = $original->getOriginalPrice())
            $this->originalPrice = $originalPrice;

        if ($discountAmount = $original->getDiscountAmount()) 
            $this->discount = $discountAmount;
        
        if ($taxAmount = $original->getTaxAmount()) 
            $this->tax = $taxAmount;
        
        // Shipping is only available for quotes and orders, but not for items
        if ($original instanceOf Mage_Sales_Model_Quote || $original instanceOf Mage_Sales_Model_Order)
        {
            // Get the shipping amount
            if ($shippingAmount = $original->getShippingAmount())
                $this->shipping = $shippingAmount;
            else
                $this->shipping = 0;
        }
        else $this->shipping = 0;
        
        if ($currency = $original->getOrderCurrencyCode()) 
            $this->currency = $currency;
        elseif ($currency = $original->getQuoteCurrencyCode())
            $this->currency = $currency;
        elseif (($order = $original->getOrder()) && ($currency = $order->getOrderCurrencyCode()))
            $this->currency = $currency;
        elseif (($quote = $original->getQuote()) && ($currency = $quote->getQuoteCurrencyCode()))
            $this->currency = $currency;
        else
            $this->currency = '';
        
        return $this;
    }

    /**
     *  Return the total price
     *  @return     float
     */
    public function total()
    {
        return $this->total;
    }
    
    /**
     *  Return the price for the individual item
     *  @return     float
     */
    public function costs()
    {
        return $this->costs;
    }
    
    /**
     *  Return the price for the individual item
     *  @return     float
     */
    public function itemPrice()
    {
        return $this->itemPrice;
    }
    
    /**
     *  Return the original price for the individual item
     *  @return     float
     */
    public function originalPrice()
    {
        return $this->originalPrice;
    }
    
    /**
     *  Return the discount which was given
     *  @return     float
     */
    public function discount()
    {
        return $this->discount;
    }
    
    /**
     *  Return the tax which was paid
     *  @return     float
     */
    public function tax()
    {
        return $this->tax;
    }
    
    /**
     *  Return the shipping costs
     *  @return     float
     */
    public function shipping()
    {
        return $this->shipping;
    }
    
    /**
     *  Return the currency code
     *  @return     float
     */
    public function currency()
    {
        return $this->currency;
    }

    /**
     *  Serialize the object
     *  @return     string
     */
    public function serialize()
    {
        // serialize the data
        return serialize(array(
            $this->total(),
            $this->costs(),
            $this->itemPrice(),
            $this->originalPrice(),
            $this->discount(),
            $this->tax(),
            $this->shipping(),
            $this->currency(),
        ));
    }

    /**
     *  Unserialize the object
     *  @param      string
     *  @return     Copernica_MarketingSoftware_Model_Abstraction_Price
     */
    public function unserialize($string)
    {
        list(
            $this->total,
            $this->costs,
            $this->itemPrice,
            $this->originalPrice,
            $this->discount,
            $this->tax,
            $this->shipping,
            $this->currency
        ) = unserialize($string);
    }
}

