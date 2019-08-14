<?php
/**
 *  A wrapper object around an Order Item
 */
class Copernica_MarketingSoftware_Model_Abstraction_Order_Item implements Serializable
{
    /**
     *  The original object
     *  @param      Mage_Sales_Model_Order_Item
     */
    private $original;

    /**
     * Predefine the internal fields
     */
    private $id;
    private $orderId;
    private $quantity;
    private $price;
    private $weight;
    private $timestamp;
    private $options;
    private $product;

    /**
     *  Sets the original model
     *  @param      Mage_Sales_Model_Order_Item $original
     *  @return     Copernica_MarketingSoftware_Model_Abstraction_Order_Item
     */
    public function setOriginal(Mage_Sales_Model_Order_Item $original)
    {
        $this->original = $original;
        return $this;
    }

    /**
     *  The id of this order item object
     *  @return     integer
     */
    public function id()
    {
        // Is this object still present?
        if (is_object($this->original))
        {
            return $this->original->getId();
        }
        else return $this->id;
    }

    /**
     *  Get the order to which this item belongs
     *  @return     Copernica_MarketingSoftware_Model_Abstraction_Order
     */
    public function order()
    {
        // Is this object still present?
        if (is_object($this->original))
        {
            if ($order = $this->original->getOrder()) {
                return Mage::getModel('marketingsoftware/abstraction_order')->setOriginal($order);
            } else {
                return null;
            }
        }
        else return Mage::getModel('marketingsoftware/abstraction_order')->loadOrder($this->orderId);
    }

    /**
     *  The amount of this order item
     *  @return     integer
     */
    public function quantity()
    {
        // Is this object still present?
        if (is_object($this->original))
        {
            return $this->original->getQtyOrdered();
        }
        else return $this->quantity;
    }

    /**
     *  The price
     *  Note that an object is returned, which may consist of multiple components
     *  @return     Copernica_MarketingSoftware_Model_Abstraction_Price
     */
    public function price()
    {
        // Is this object still present?
        if (is_object($this->original))
        {
            // Note that the price may consist of multiple elements
            return Mage::getModel('marketingsoftware/abstraction_price')->setOriginal($this->original);
        }
        else return $this->price;
    }

    /**
     *  The weight
     *  @return     float
     */
    public function weight()
    {
        // Is this object still present?
        if (is_object($this->original))
        {
            return $this->original->getWeight();
        }
        else return $this->weight;
    }

    /**
     *  The timestamp at which this order was modified
     *  @return     string
     */
    public function timestamp()
    {
        // Is this object still present?
        if (is_object($this->original))
        {
            return $this->original->getUpdatedAt();
        }
        else return $this->timestamp;
    }

    /**
     *  Get the options of this order item
     *  @return     Copernica_MarketingSoftware_Model_Abstraction_Order_Item_Options
     */
    public function options()
    {
        // Is this object still present?
        if (is_object($this->original))
        {
            $options = Mage::getModel('marketingsoftware/abstraction_order_item_options')->setOriginal($this->original);
            if ($options->attributes()) {
                //only return option object if it this order actually has options
                return $options;
            } else {
                return null;
            }
        }
        else return $this->options;
    }

    /**
     *  Get the product which belongs to this item
     *  @return     Copernica_MarketingSoftware_Model_Abstraction_Product
     */
    public function product()
    {
        // Is this object still present?
        if (is_object($this->original))
        {
            return Mage::getModel('marketingsoftware/abstraction_product')->setOriginal($this->original);
        }
        else return $this->product;
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

