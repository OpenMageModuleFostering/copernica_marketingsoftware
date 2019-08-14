<?php
/**
 *  A wrapper object around a Quote Item
 */
class Copernica_MarketingSoftware_Model_Abstraction_Quote_Item implements Serializable
{
    /**
     *  The original object
     *  @param      Mage_Sales_Model_Quote_Item
     */
    private $original;

    /**
     * Predefine the internal fields
     */
    private $id;
    private $quoteId;
    private $storeId;
    private $quantity;
    private $price;
    private $weight;
    private $timestamp;
    private $options;
    private $product;

    /**
     *  Sets the original model
     *  @param      Mage_Sales_Model_Quote_Item $original
     *  @return     Copernica_MarketingSoftware_Model_Abstraction_Quote_Item
     */
    public function setOriginal(Mage_Sales_Model_Quote_Item $original)
    {
        $this->original = $original;
        return $this;
    }

    /**
     *  The id of this quote item object
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
     *  Get the quote to which this item belongs
     *  @return     Copernica_MarketingSoftware_Model_Abstraction_Quote
     */
    public function quote()
    {
        // Is this object still present?
        if (is_object($this->original))
        {
            if ($quote = $this->original->getQuote()) {
                return Mage::getModel('marketingsoftware/abstraction_quote')->setOriginal($quote);
            } else {
                return null;
            }
        }
        else return Mage::getModel('marketingsoftware/abstraction_quote')->loadQuote($this->quoteId);
    }

    /**
     *  The amount of this quote item
     *  @return     integer
     */
    public function quantity()
    {
        // Is this object still present?
        if (is_object($this->original))
        {
            return $this->original->getQty();
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
     *  The timestamp at which this quote item was modified
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
     *  Get the options of this quote item
     *  @return     Copernica_MarketingSoftware_Model_Abstraction_Quote_Item_Options
     */
    public function options()
    {
        // Is this object still present?
        if (is_object($this->original))
        {
            $options = Mage::getModel('marketingsoftware/abstraction_quote_item_options')->setOriginal($this->original);
            if ($options->attributes()) {
                //only return option object if it this quote actually has options
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
            is_object($quote = $this->quote()) ? $quote->id() : null,
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
     *  @return     Copernica_MarketingSoftware_Model_Abstraction_Quote_Item
     */
    public function unserialize($string)
    {
        list(
            $this->id,
            $this->quoteId,
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