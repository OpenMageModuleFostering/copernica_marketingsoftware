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
    protected $_total;
    protected $_costs;
    protected $_itemPrice;
    protected $_originalPrice;
    protected $_discount;
    protected $_tax;
    protected $_shipping;
    protected $_currency;

    /**
     *  Sets the original model
     *  
     *  @param    Mage_Sales_Model_Quote|Mage_Sales_Model_Order|Mage_Sales_Model_Quote_Item|Mage_Sales_Model_Order_Item $original
     *  @return    Copernica_MarketingSoftware_Model_Abstraction_Price
     */
    public function setOriginal($original)
    {
        if ($grandTotal = $original->getGrandTotal()) {
            $this->_total = $grandTotal;
        } elseif ($rowTotalInclTax = $original->getRowTotalInclTax()) {
            $this->_total = $rowTotalInclTax;
        } elseif ($baseRowTotal = $original->getBaseRowTotal()) {
            $this->_total = $baseRowTotal;
        }
        
        if ($original instanceOf Mage_Sales_Model_Quote || $original instanceOf Mage_Sales_Model_Order) {
            $costs = 0;
        
            foreach ($original->getAllVisibleItems() as $item) {
                $costs += $item->getBaseCost();
            }
        
            $this->_costs = $costs;
        } elseif ($baseCost = $original->getBaseCost()) {
            $this->_costs = $baseCost;
        }
        
        if ($original instanceOf Mage_Sales_Model_Quote || $original instanceOf Mage_Sales_Model_Order) {
            $this->_itemPrice = 0;
        } elseif ($price = $original->getPrice()) {
            $this->_itemPrice = $price;
        }
        
        if ($original instanceOf Mage_Sales_Model_Quote || $original instanceOf Mage_Sales_Model_Order) {
            $this->_originalPrice = 0;
        } elseif ($original instanceOf Mage_Sales_Model_Quote_Item) {
            $this->_originalPrice = 0;
        } elseif ($originalPrice = $original->getOriginalPrice()) {
            $this->_originalPrice = $originalPrice;
        }

        if ($discountAmount = $original->getDiscountAmount()) { 
            $this->_discount = $discountAmount;
        }
        
        if ($taxAmount = $original->getTaxAmount()) { 
            $this->_tax = $taxAmount;
        }
        
        if ($original instanceOf Mage_Sales_Model_Quote || $original instanceOf Mage_Sales_Model_Order) {
            if ($shippingAmount = $original->getShippingAmount()) {
                $this->_shipping = $shippingAmount;
            } else {
                $this->_shipping = 0;
            }
        } else { 
            $this->_shipping = 0;
        }
        
        if ($currency = $original->getOrderCurrencyCode()) { 
            $this->_currency = $currency;
        } elseif ($currency = $original->getQuoteCurrencyCode()) {
            $this->_currency = $currency;
        } elseif (($order = $original->getOrder()) && ($currency = $order->getOrderCurrencyCode())) {
            $this->_currency = $currency;
        } elseif (($quote = $original->getQuote()) && ($currency = $quote->getQuoteCurrencyCode())) {
            $this->_currency = $currency;
        } else {
            $this->_currency = '';
        }
        
        return $this;
    }

    /**
     *  Return the total price
     *  
     *  @return    float
     */
    public function total()
    {
        return $this->_total;
    }
    
    /**
     *  Return the price for the individual item
     *  
     *  @return    float
     */
    public function costs()
    {
        return $this->_costs;
    }
    
    /**
     *  Return the price for the individual item
     *  
     *  @return    float
     */
    public function itemPrice()
    {
        return $this->_itemPrice;
    }
    
    /**
     *  Return the original price for the individual item
     *  
     *  @return    float
     */
    public function originalPrice()
    {
        return $this->_originalPrice;
    }
    
    /**
     *  Return the discount which was given
     *  
     *  @return    float
     */
    public function discount()
    {
        return $this->_discount;
    }
    
    /**
     *  Return the tax which was paid
     *  
     *  @return    float
     */
    public function tax()
    {
        return $this->_tax;
    }
    
    /**
     *  Return the shipping costs
     *  
     *  @return    float
     */
    public function shipping()
    {
        return $this->_shipping;
    }
    
    /**
     *  Return the currency code
     *  
     *  @return    float
     */
    public function currency()
    {
        return $this->_currency;
    }

    /**
     *  Serialize the object
     *  
     *  @return    string
     */
    public function serialize()
    {
        return serialize(
            array(
            $this->total(),
            $this->costs(),
            $this->itemPrice(),
            $this->originalPrice(),
            $this->discount(),
            $this->tax(),
            $this->shipping(),
            $this->currency(),
            )
        );
    }

    /**
     *  Unserialize the object
     *  
     *  @param    string    $string
     *  @return    Copernica_MarketingSoftware_Model_Abstraction_Price
     */
    public function unserialize($string)
    {
        list(
            $this->_total,
            $this->_costs,
            $this->_itemPrice,
            $this->_originalPrice,
            $this->_discount,
            $this->_tax,
            $this->_shipping,
            $this->_currency
        ) = unserialize($string);
        
        return $this;
    }
}

