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
 *  Brigde class between Copernica subprofile and magento wishlist item
 */
class Copernica_MarketingSoftware_Model_Copernica_Entity_Wishlist_Item extends Copernica_MarketingSoftware_Model_Copernica_Entity_Product
{
    
    /**
     *  Cached item
     *
     *  @var    Mage_Wishlist_Model_Item
     */
    protected $_wishlistItem = null;
    
    /**
     *  Fetch wishlist item Id
     *
     *  @return string
     */
    public function fetchId()
    {
        return $this->_wishlistItem->getId();
    }
    
    /**
     *  Fetch wishlist Id
     *
     *  @return string
     */
    public function fetchWishlistId()
    {
        return $this->_wishlistItem->getWishlistId();
    }    
    
    /**
     *  Fetch quantity
     *
     *  @return string
     */
    public function fetchDescription()
    {
        return $this->_wishlistItem->getDescription();
    }
    
    /**
     *  Fetch quantity
     *
     *  @return string
     */
    public function fetchQuantity()
    {
        return $this->_wishlistItem->getQty();
    }    
    
    /**
     *  Fetch total
     *
     *  @return string
     */
    public function fetchTotalPrice()
    {
        return $this->_wishlistItem->getProduct()->getPrice() * $this->getQuantity();
    }
    
    /**
     *  Fetch price
     *
     *  @return string
     */
    public function fetchPrice()
    {
        return $this->_wishlistItem->getProduct()->getPrice();
    }
    
    /**
     *  Fetch timestamp
     *
     *  @return string
     */
    public function getCreatedAt()
    {
        return $this->_wishlistItem->getAddedAt();
    }
    
    /**
     *  Fetch store view
     *
     *  @return string
     */
    public function fetchStoreView()
    {
        $store = Mage::getModel('core/store')->load($this->getStoreId());
         
        return Mage::getModel('marketingsoftware/abstraction_storeview')->setOriginal($store);
    }
    
    /**
     *  Get store Id
     *
     *  @return int
     */
    public function getStoreId()
    {
        return $this->_wishlistItem->getStoreId();
    }    
    
    /**
     *  Get REST wishlist item entity
     *  
     *  @return Copernica_MarketingSoftware_Model_Rest_Wishlist_Item
     */
    public function getRestWishlistItem()
    {
        $restWishlistItem = Mage::getModel('marketingsoftware/rest_wishlist_item');
        $restWishlistItem->setWishlistItemEntity($this);
         
        return $restWishlistItem;
    }
    
    /**
     *  Set copernica wishlist item
     *
     *  @param    Mage_Wishlist_Model_Item    $wishlistItem
     */
    public function setWishlistItem(Mage_Wishlist_Model_Item $wishlistItem)
    {
        $this->_wishlistItem = $wishlistItem;
        
        $this->setProduct($wishlistItem->getProductId(), $this->getStoreId());
    }
}