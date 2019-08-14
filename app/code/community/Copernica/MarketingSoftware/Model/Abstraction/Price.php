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
     *  The original object
     *  @param      Mage_Sales_Model_Quote|Mage_Sales_Model_Order|Mage_Sales_Model_Quote_Item|Mage_Sales_Model_Order_Item
     */
    protected $original;
    
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
        $this->original = $original;
        return $this;
    }

    /**
     *  Return the total price
     *  @return     float
     */
    public function total()
    {
        // Is this object still present?
        if (is_object($this->original))
        {
            // Used for quotes and orders
            if ($grandTotal = $this->original->getGrandTotal())                 return $grandTotal;
            
            // Used for quote items and order items
            elseif ($rowTotalInclTax = $this->original->getRowTotalInclTax())   return $rowTotalInclTax;
            
            // Used for quote items and order items (when no tax is configured)
            elseif ($baseRowTotal = $this->original->getBaseRowTotal())         return $baseRowTotal;
        }
        else return $this->total;
    }
    
    /**
     *  Return the price for the individual item
     *  @return     float
     */
    public function costs()
    {
        // Is this object still present?
        if (is_object($this->original))
        {
            // Used for quotes and orders
            if ($this->original instanceOf Mage_Sales_Model_Quote || $this->original instanceOf Mage_Sales_Model_Order)
            {
                $costs = 0;
                
                // iterate over all visisble items
                foreach ($this->original->getAllVisibleItems() as $item)
                {
                    $costs += $item->getBaseCost();
                }
                
                // return the costs
                return $costs;
            }
        
            // Used for quote items and order items
            elseif ($baseCost = $this->original->getBaseCost()) return $baseCost;
        }
        else return $this->costs;
    }
    
    /**
     *  Return the price for the individual item
     *  @return     float
     */
    public function itemPrice()
    {
        // Is this object still present?
        if (is_object($this->original))
        {
            // no price for an individual item
            if ($this->original instanceOf Mage_Sales_Model_Quote || $this->original instanceOf Mage_Sales_Model_Order) return 0;            
            
            // Used for quote items and order items
            elseif ($price = $this->original->getPrice()) return $price;
        }
        else return $this->itemPrice;
    }
    
    /**
     *  Return the original price for the individual item
     *  @return     float
     */
    public function originalPrice()
    {
        // Is this object still present?
        if (is_object($this->original))
        {
            // no price for an individual item
            if ($this->original instanceOf Mage_Sales_Model_Quote || $this->original instanceOf Mage_Sales_Model_Order) return 0;            
            
            // Used for quote items and order items
            elseif ($originalPrice = $this->original->getOriginalPrice()) return $originalPrice;
        }
        else return $this->originalPrice;
    }
    
    /**
     *  Return the discount which was given
     *  @return     float
     */
    public function discount()
    {
        // Is this object still present?
        if (is_object($this->original))
        {
            // Used for all items
            if ($discountAmount = $this->original->getDiscountAmount()) return $discountAmount;
        }
        else return $this->discount;
    }
    
    /**
     *  Return the tax which was paid
     *  @return     float
     */
    public function tax()
    {
        // Is this object still present?
        if (is_object($this->original))
        {
            // Used for all items
            if ($taxAmount = $this->original->getTaxAmount()) return $taxAmount;
        }
        else return $this->tax;
    }
    
    /**
     *  Return the shipping costs
     *  @return     float
     */
    public function shipping()
    {
        // Is this object still present?
        if (is_object($this->original))
        {
            // Shipping is only available for quotes and orders, but not for items
            if ($this->original instanceOf Mage_Sales_Model_Quote || $this->original instanceOf Mage_Sales_Model_Order)
            {
                // Get the shipping amount
                if ($shippingAmount = $this->original->getShippingAmount()) return $shippingAmount;
            }
            else return 0;            
        }
        else return $this->shipping;
    }
    
    /**
     *  Return the currency code
     *  @return     float
     */
    public function currency()
    {
        // Is this object still present?
        if (is_object($this->original))
        {
            if ($currency = $this->original->getOrderCurrencyCode()) return $currency;
            elseif ($currency = $this->original->getQuoteCurrencyCode()) return $currency;
            elseif (($order = $this->original->getOrder()) && ($currency = $order->getOrderCurrencyCode())) return $currency;
            elseif (($quote = $this->original->getQuote()) && ($currency = $quote->getQuoteCurrencyCode())) return $currency;
            else return '';            
        }
        else return $this->currency;
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

