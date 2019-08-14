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
 *  A wrapper object around an Order Item
 */
class Copernica_MarketingSoftware_Model_Abstraction_Order_Item implements Serializable
{
    /**
     * Predefine the internal fields
     */
    protected $_id;
    protected $_orderId;
    protected $_quantity;
    protected $_price;
    protected $_weight;
    protected $_timestamp;
    protected $_options;
    protected $_product;

    /**
     *  Sets the original model
     *  
     *  @param	Mage_Sales_Model_Order_Item	$original
     *  @return	Copernica_MarketingSoftware_Model_Abstraction_Order_Item
     */
    public function setOriginal(Mage_Sales_Model_Order_Item $original)
    {
        $this->_id = $original->getId();
        $this->_orderId = $original->getOrder()->getId();
        $this->_quantity = $original->getQtyOrdered();
        $this->_price = Mage::getModel('marketingsoftware/abstraction_price')->setOriginal($original);
        $this->_weight = $original->getWeight();
        $this->_timestamp = $original->getUpdatedAt();
        $this->_product = Mage::getModel('marketingsoftware/abstraction_product')->setOriginal($original);
        
        $options = Mage::getModel('marketingsoftware/abstraction_order_item_options')->setOriginal($original);
        
        if ($options->attributes()) {
            $this->_options = $options;
        } 
        
        return $this;
    }

    /**
     *  The id of this order item object
     *  
     *  @return	integer
     */
    public function id()
    {
        return $this->_id;
    }

    /**
     *  Get the order to which this item belongs
     *  
     *  @return	Copernica_MarketingSoftware_Model_Abstraction_Order
     */
    public function order()
    {
        return Mage::getModel('marketingsoftware/abstraction_order')->loadOrder($this->_orderId);
    }

    /**
     *  The amount of this order item
     *  
     *  @return	integer
     */
    public function quantity()
    {
        return $this->_quantity;
    }

    /**
     *  The price
     *  Note that an object is returned, which may consist of multiple components
     *  
     *  @return	Copernica_MarketingSoftware_Model_Abstraction_Price
     */
    public function price()
    {
        return $this->_price;
    }

    /**
     *  The weight
     *  
     *  @return	float
     */
    public function weight()
    {
        return $this->_weight;
    }

    /**
     *  The timestamp at which this order was modified
     *  
     *  @return	string
     */
    public function timestamp()
    {
        return $this->_timestamp;
    }

    /**
     *  Get the options of this order item
     *  
     *  @return	Copernica_MarketingSoftware_Model_Abstraction_Order_Item_Options
     */
    public function options()
    {
        return $this->_options;
    }

    /**
     *  Get the product which belongs to this item
     *  
     *  @return	Copernica_MarketingSoftware_Model_Abstraction_Product
     */
    public function product()
    {
        return $this->_product;
    }

    /**
     *  Serialize the object
     *  
     *  @return	string
     */
    public function serialize()
    {
        return serialize(array(
            $this->_id(),
            is_object($order = $this->order()) ? $order->id() : null,
            $this->_quantity(),
            $this->_price(),
            $this->_weight(),
            $this->_timestamp(),
            $this->_options(),
            $this->_product(),
        ));
    }

    /**
     *  Unserialize the object
     *  
     *  @param	string	$string
     *  @return	Copernica_MarketingSoftware_Model_Abstraction_Order_Item
     */
    public function unserialize($string)
    {
        list(
            $this->_id,
            $this->_orderId,
            $this->_quantity,
            $this->_price,
            $this->_weight,
            $this->_timestamp,
            $this->_options,
            $this->_product
        ) = unserialize($string);
        
        return $this;
    }
}

