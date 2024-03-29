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
 *  A wrapper object around a wishlist Item
 */
class Copernica_MarketingSoftware_Model_Abstraction_Wishlist_Item implements Serializable
{
    /**
     * Predefine the internal fields
     */
    protected $_id;
    protected $_wishlistId;
    protected $_quantity;
    protected $_price;
    protected $_weight;
    protected $_timestamp;
    protected $_options;
    protected $_product;

    /**
     *  Sets the original model
     *  
     *  @param    Mage_Wishlist_Model_Item    $original
     *  @return    Copernica_MarketingSoftware_Model_Abstraction_Wishlist_Item
     */
    public function setOriginal(Mage_Wishlist_Model_Item $original)
    {
        $this->_id = $original->getId();
        $this->_wishlistId = $original->getWishlist()->getId();
        $this->_quantity = $original->getQty();
        $this->_price = Mage::getModel('marketingsoftware/abstraction_price')->setOriginal($original);
        $this->_weight = $original->getWeight();
        $this->_timestamp = $original->getUpdatedAt();
        $this->_product = Mage::getModel('marketingsoftware/abstraction_product')->setOriginal($original);
        
        $options = Mage::getModel('marketingsoftware/abstraction_wishlist_item_options')->setOriginal($original);
        
        if ($options->attributes()) {
            $this->_options = $options;
        }
        
        return $this;
    }

    /**
     *  The id of this wishlist item object
     *  
     *  @return    integer
     */
    public function id()
    {
        return $this->_id;
    }

    /**
     *  Get the wishlist to which this item belongs
     *  
     *  @return    Copernica_MarketingSoftware_Model_Abstraction_Wishlist
     */
    public function wishlist()
    {
        return Mage::getModel('marketingsoftware/abstraction_wishlist')->loadWishlist($this->_wishlistId);
    }

    /**
     *  The amount of this wishlist item
     *  
     *  @return    integer
     */
    public function quantity()
    {
        return $this->_quantity;
    }

    /**
     *  The price
     *  Note that an object is returned, which may consist of multiple components
     *  
     *  @return    Copernica_MarketingSoftware_Model_Abstraction_Price
     */
    public function price()
    {
        return $this->_price;
    }

    /**
     *  The weight
     *  
     *  @return    float
     */
    public function weight()
    {
        return $this->_weight;
    }

    /**
     *  The timestamp at which this wishlist item was modified
     *  
     *  @return    string
     */
    public function timestamp()
    {
        return $this->_timestamp;
    }

    /**
     *  Get the options of this wishlist item
     *  
     *  @return    Copernica_MarketingSoftware_Model_Abstraction_Wishlist_Item_Options
     */
    public function options()
    {
        return $this->_options;
    }

    /**
     *  Get the product which belongs to this item
     *  
     *  @return    Copernica_MarketingSoftware_Model_Abstraction_Product
     */
    public function product()
    {
        return $this->_product;
    }

    /**
     *  Loads a wishlist model
     *
     *  @param    integer    $wishlistId
     *  @return    Copernica_MarketingSoftware_Model_Abstraction_Wishlist_Item
     */
    public function loadWishlistItem($wishlistItemId)
    {
        $wishlistItem = Mage::getModel('wishlist/item')->load($wishlistItemId);
        
        if ($wishlistItem->getId()) {
            $this->setOriginal($wishlistItem);
        } else {
            $this->id = $wishlistItemId;
        }
        
        return $this;
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
            $this->id(),
            is_object($wishlist = $this->wishlist()) ? $wishlist->id() : null,
            $this->quantity(),
            $this->price(),
            $this->weight(),
            $this->timestamp(),
            $this->options(),
            $this->product(),
            )
        );
    }

    /**
     *  Unserialize the object
     *  
     *  @param    string    $string
     *  @return    Copernica_MarketingSoftware_Model_Abstraction_Wishlist_Item
     */
    public function unserialize($string)
    {
        list(
            $this->_id,
            $this->_wishlistId,
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