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
 *  A wrapper object around an Order Item
 */
class Copernica_MarketingSoftware_Model_Abstraction_Order_Item implements Serializable
{
    /**
     * Predefine the internal fields
     */
    protected $id;
    protected $orderId;
    protected $quantity;
    protected $price;
    protected $weight;
    protected $timestamp;
    protected $options;
    protected $product;

    /**
     *  Sets the original model
     *  @param      Mage_Sales_Model_Order_Item $original
     *  @return     Copernica_MarketingSoftware_Model_Abstraction_Order_Item
     */
    public function setOriginal(Mage_Sales_Model_Order_Item $original)
    {
    	$this->id = $original->getId();
    	$this->orderId = $original->getOrder()->getId();
    	$this->quantity = $original->getQtyOrdered();
    	$this->price = Mage::getModel('marketingsoftware/abstraction_price')->setOriginal($original);
    	$this->weight = $original->getWeight();
    	$this->timestamp = $original->getUpdatedAt();
    	$this->product = Mage::getModel('marketingsoftware/abstraction_product')->setOriginal($original);
    	
    	$options = Mage::getModel('marketingsoftware/abstraction_order_item_options')->setOriginal($original);
    	if ($options->attributes()) {
    		//only return option object if it this order actually has options
    		$this->options = $options;
    	} 
    	
        return $this;
    }

    /**
     *  The id of this order item object
     *  @return     integer
     */
    public function id()
    {
        return $this->id;
    }

    /**
     *  Get the order to which this item belongs
     *  @return     Copernica_MarketingSoftware_Model_Abstraction_Order
     */
    public function order()
    {
        return Mage::getModel('marketingsoftware/abstraction_order')->loadOrder($this->orderId);
    }

    /**
     *  The amount of this order item
     *  @return     integer
     */
    public function quantity()
    {
        return $this->quantity;
    }

    /**
     *  The price
     *  Note that an object is returned, which may consist of multiple components
     *  @return     Copernica_MarketingSoftware_Model_Abstraction_Price
     */
    public function price()
    {
        return $this->price;
    }

    /**
     *  The weight
     *  @return     float
     */
    public function weight()
    {
        return $this->weight;
    }

    /**
     *  The timestamp at which this order was modified
     *  @return     string
     */
    public function timestamp()
    {
        return $this->timestamp;
    }

    /**
     *  Get the options of this order item
     *  @return     Copernica_MarketingSoftware_Model_Abstraction_Order_Item_Options
     */
    public function options()
    {
        return $this->options;
    }

    /**
     *  Get the product which belongs to this item
     *  @return     Copernica_MarketingSoftware_Model_Abstraction_Product
     */
    public function product()
    {
        return $this->product;
    }

    /**
     *  Serialize the object
     *  @return     string
     */
    public function serialize()
    {
        // serialize the data
        return serialize(array(
            $this->id(),
            is_object($order = $this->order()) ? $order->id() : null,
            $this->quantity(),
            $this->price(),
            $this->weight(),
            $this->timestamp(),
            $this->options(),
            $this->product(),
        ));
    }

    /**
     *  Unserialize the object
     *  @param      string
     *  @return     Copernica_MarketingSoftware_Model_Abstraction_Order_Item
     */
    public function unserialize($string)
    {
        list(
            $this->id,
            $this->orderId,
            $this->quantity,
            $this->price,
            $this->weight,
            $this->timestamp,
            $this->options,
            $this->product
        ) = unserialize($string);
        return $this;
    }
}

