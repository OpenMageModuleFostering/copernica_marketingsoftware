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
 *  Bridge between magento wishlist and copernica subprofile
 */
class Copernica_MarketingSoftware_Model_Copernica_Entity_Wishlist extends Copernica_MarketingSoftware_Model_Copernica_Entity
{
    /**
     *  Cached wishlist instance
     *  
     *  @var    Mage_Wishlist_Model_Wishlist
     */
    protected $_wishlist;

    /**
     *  Cached wishlist items
     *  
     *  @var    array
     */
    protected $_wishlistItems;

    /**
     *  Fetch wishlist Id
     *
     *  @return string
     */
    public function fetchId()
    {
        return $this->_wishlist->getId();
    }
        
    /**
     *  @return array
     */
    public function getItems()
    {
        if (!is_null($this->_wishlistItems)) {
            return $this->_wishlistItems;
        }

        $wishlistItems = array();

        foreach ($this->_wishlist->getItemCollection() as $wishlistItem) {
            $wishlistItemEntity = Mage::getModel('marketingsoftware/copernica_entity_wishlist_item');
            $wishlistItemEntity->setWishlistItem($wishlistItem);
                        
            $wishlistItems[] = $wishlistItemEntity;
        }

        return $this->_wishlistItems = $wishlistItems;
    }

    /**
     *  Get REST wishlist entity
     *  
     *  @return Copernica_MarketingSoftware_Model_Rest_Wishlist
     */ 
    public function getRestWishlist()
    {
        $restWishlist = Mage::getModel('marketingsoftware/rest_wishlist');
        $restWishlist->setWishlistEntity($this);
         
        return $restWishlist;
    }
    
    /**
     *  Set wishlist entity
     *
     *  @param    Mage_Wishlist_Model_Wishlist    $wishlist
     */
    public function setWishlist(Mage_Wishlist_Model_Wishlist $wishlist)
    {
        $this->_wishlist = $wishlist;
    }
}